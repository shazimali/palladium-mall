<?php

namespace App\Jobs;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateLatePaymentFines implements ShouldQueue
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
        $date = $this->targetDate ? $this->targetDate->copy() : Carbon::today();
        $todayDateString = $date->toDateString();

        // Get all unpaid or partially paid rent/maintenance bills where due date is in the past
        $overduePayments = Payment::whereIn('status', ['unpaid', 'partial'])
            ->whereIn('type', ['rent', 'maintenance'])
            ->where('due_date', '<', $todayDateString)
            ->with(['agreement', 'tenant', 'unit'])
            ->get();

        if ($overduePayments->isEmpty()) {
            Log::info("GenerateLatePaymentFines executed. No overdue payments found.");
            return;
        }

        // Group overdue bills by agreement_id and billing month so we consolidate daily fines
        $groups = $overduePayments->groupBy(function ($payment) {
            return $payment->agreement_id . '_' . $payment->month->toDateString();
        });

        $fineCreated = 0;
        $fineUpdated = 0;

        DB::transaction(function () use ($groups, $date, $todayDateString, &$fineCreated, &$fineUpdated) {
            foreach ($groups as $key => $payments) {
                $firstPayment = $payments->first();
                $agreement = $firstPayment->agreement;

                // Validate if agreement has daily fine terms set
                if (!$agreement || !($agreement->fine_per_day > 0)) {
                    continue;
                }

                $dueDate = Carbon::parse($firstPayment->due_date);
                $graceDays = $agreement->grace_period_days ?: 0;
                
                // Fine starts after: due_date + grace_period_days
                $fineStartDate = $dueDate->copy()->addDays($graceDays);

                // We only generate a fine if the current date is strictly past the fine start date
                if ($date->gt($fineStartDate)) {
                    // Calculate days late: from due date to current date
                    $daysLate = $dueDate->diffInDays($date);
                    $totalFine = $daysLate * (float) $agreement->fine_per_day;

                    // Locate if a fine record already exists for this agreement & month
                    $fineRecord = Payment::where('agreement_id', $agreement->id)
                        ->where('type', 'fine')
                        ->where('month', $firstPayment->month->toDateString())
                        ->first();

                    $notes = "Consolidated late payment fine for " . $firstPayment->month->format('M Y') . 
                             " (" . $daysLate . " days late at Rs. " . number_format($agreement->fine_per_day) . "/day)";

                    if (!$fineRecord) {
                        Payment::create([
                            'tenant_id'    => $firstPayment->tenant_id,
                            'unit_id'      => $firstPayment->unit_id,
                            'agreement_id' => $agreement->id,
                            'type'         => 'fine',
                            'month'        => $firstPayment->month->toDateString(),
                            'amount'       => $totalFine,
                            'amount_paid'  => 0,
                            'status'       => 'unpaid',
                            'due_date'     => $todayDateString,
                            'notes'        => $notes,
                        ]);
                        $fineCreated++;
                    } else {
                        // Update the existing fine record if it is not fully paid yet
                        if (!$fineRecord->isPaid()) {
                            $fineRecord->update([
                                'amount'   => $totalFine,
                                'notes'    => $notes,
                                'due_date' => $todayDateString,
                                'status'   => Payment::calculateStatus($totalFine, $fineRecord->amount_paid),
                            ]);
                            $fineUpdated++;
                        }
                    }
                }
            }
        });

        Log::info("GenerateLatePaymentFines executed. Fines created: {$fineCreated}, Fines updated: {$fineUpdated}");
    }
}
