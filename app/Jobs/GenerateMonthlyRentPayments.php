<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyRentPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?Carbon $targetDate;

    /**
     * Create a new job instance.
     */
    public function __construct(?Carbon $targetDate = null)
    {
        $this->targetDate = $targetDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $date = $this->targetDate ? $this->targetDate->copy() : Carbon::now();
        $billingMonth = $date->startOfMonth();
        $monthStr = $billingMonth->toDateString();

        // Get all active tenants with active agreements
        $tenants = Tenant::where('status', 'active')
            ->with(['activeAgreement', 'unit'])
            ->get()
            ->filter(fn($t) => $t->activeAgreement !== null);

        $createdCount = 0;

        DB::transaction(function () use ($tenants, $billingMonth, $monthStr, &$createdCount) {
            foreach ($tenants as $tenant) {
                $agreement = $tenant->activeAgreement;

                // Validate if billing month falls within active agreement start and end dates (inclusive of month bounds)
                $agreementStart = $agreement->start_date->copy()->startOfMonth();
                $agreementEnd = $agreement->end_date->copy()->startOfMonth();

                if ($billingMonth->lt($agreementStart) || $billingMonth->gt($agreementEnd)) {
                    continue;
                }

                // Determine due date
                $dueDay = $agreement->payment_due_day ?: 10;
                $daysInMonth = $billingMonth->daysInMonth;
                $clampedDueDay = min($dueDay, $daysInMonth);
                $dueDate = $billingMonth->copy()->day($clampedDueDay)->toDateString();

                // 1. Generate Rent Payment if not exists
                $rentExists = Payment::where('tenant_id', $tenant->id)
                    ->where('agreement_id', $agreement->id)
                    ->where('type', 'rent')
                    ->where('month', $monthStr)
                    ->exists();

                if (!$rentExists) {
                    Payment::create([
                        'tenant_id'    => $tenant->id,
                        'unit_id'      => $tenant->unit_id,
                        'agreement_id' => $agreement->id,
                        'type'         => 'rent',
                        'month'        => $monthStr,
                        'amount'       => $agreement->monthly_rent,
                        'amount_paid'  => 0,
                        'status'       => 'unpaid',
                        'due_date'     => $dueDate,
                    ]);
                    $createdCount++;
                }

                // 2. Generate Maintenance Payment if not exists and has charge > 0
                if ($agreement->maintenance_charge > 0) {
                    $maintExists = Payment::where('tenant_id', $tenant->id)
                        ->where('agreement_id', $agreement->id)
                        ->where('type', 'maintenance')
                        ->where('month', $monthStr)
                        ->exists();

                    if (!$maintExists) {
                        Payment::create([
                            'tenant_id'    => $tenant->id,
                            'unit_id'      => $tenant->unit_id,
                            'agreement_id' => $agreement->id,
                            'type'         => 'maintenance',
                            'month'        => $monthStr,
                            'amount'       => $agreement->maintenance_charge,
                            'amount_paid'  => 0,
                            'status'       => 'unpaid',
                            'due_date'     => $dueDate,
                        ]);
                        $createdCount++;
                    }
                }
            }
        });

        // ── is_self units: generate maintenance-only payments ──────────────
        // These units are owned by external persons; no tenant/agreement needed.
        $selfUnits = Unit::where('is_self', true)
            ->whereNotNull('self_maintenance_charge')
            ->where('self_maintenance_charge', '>', 0)
            ->get();

        // Determine a shared due date for self-unit maintenance (10th of month)
        $selfDueDay   = 10;
        $daysInMonth  = $billingMonth->copy()->daysInMonth;
        $selfDueDate  = $billingMonth->copy()->day(min($selfDueDay, $daysInMonth))->toDateString();

        foreach ($selfUnits as $selfUnit) {
            $exists = Payment::where('unit_id', $selfUnit->id)
                ->where('type', 'maintenance')
                ->where('month', $monthStr)
                ->exists();

            if (! $exists) {
                Payment::create([
                    'tenant_id'    => null,
                    'unit_id'      => $selfUnit->id,
                    'agreement_id' => null,
                    'type'         => 'maintenance',
                    'month'        => $monthStr,
                    'amount'       => $selfUnit->self_maintenance_charge,
                    'amount_paid'  => 0,
                    'status'       => 'unpaid',
                    'due_date'     => $selfDueDate,
                ]);
                $createdCount++;
            }
        }

        Log::info("GenerateMonthlyRentPayments executed. Total generated payment records: {$createdCount}");
    }
}
