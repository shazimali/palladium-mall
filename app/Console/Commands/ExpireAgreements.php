<?php

namespace App\Console\Commands;

use App\Models\Agreement;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireAgreements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agreements:expire
                            {--dry-run : Show what would be changed without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Mark active agreements as expired when their end_date has passed and set the associated unit to vacant.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $dryRun = $this->option('dry-run');

        // ── Pass 1: Mark overdue active agreements as expired ─────────────────
        $expiredAgreements = Agreement::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', $today)
            ->with('unit')
            ->get();

        $expiredCount = 0;
        $vacatedCount = 0;

        if ($expiredAgreements->isNotEmpty()) {
            $this->info("Pass 1: Found {$expiredAgreements->count()} active agreement(s) past end_date." . ($dryRun ? ' [DRY RUN]' : ''));

            DB::transaction(function () use ($expiredAgreements, $dryRun, &$expiredCount, &$vacatedCount) {
                foreach ($expiredAgreements as $agreement) {
                    $unitNumber = optional($agreement->unit)->unit_number ?? 'N/A';
                    $this->line("  → Agreement #{$agreement->id} | Unit: {$unitNumber} | Ended: {$agreement->end_date->toDateString()}");

                    if (! $dryRun) {
                        $agreement->update(['status' => 'expired']);
                        $expiredCount++;

                        // 1. Vacate the unit if there are no other active agreements
                        if ($agreement->unit && $agreement->unit->status === 'rented') {
                            $hasOtherActive = Agreement::where('unit_id', $agreement->unit_id)
                                ->where('id', '!=', $agreement->id)
                                ->where('status', 'active')
                                ->exists();

                            if (! $hasOtherActive) {
                                $agreement->unit->update(['status' => 'vacant']);
                                $vacatedCount++;
                            }
                        }

                        // 2. Deactivate the tenant if they have no other active agreements
                        if ($agreement->tenant) {
                            $hasOtherActiveTenantAgreement = Agreement::where('tenant_id', $agreement->tenant_id)
                                ->where('id', '!=', $agreement->id)
                                ->where('status', 'active')
                                ->exists();

                            if (! $hasOtherActiveTenantAgreement) {
                                $agreement->tenant->update([
                                    'status' => 'inactive',
                                    'unit_id' => null
                                ]);
                            }
                        }
                    }
                }
            });
        } else {
            $this->info('Pass 1: No overdue active agreements found.');
        }

        // ── Pass 2: Vacate orphaned 'rented' units (agreement already expired) ─
        // Handles the edge case where agreement was expired manually but the unit
        // status was never updated (e.g. unit 106 scenario).
        $orphanedUnits = Unit::where('status', 'rented')
            ->where('is_self', false)
            ->whereDoesntHave('agreements', fn ($q) => $q->where('status', 'active'))
            ->with('tenant')
            ->get();

        if ($orphanedUnits->isNotEmpty()) {
            $this->warn("Pass 2: Found {$orphanedUnits->count()} rented unit(s) with no active agreement." . ($dryRun ? ' [DRY RUN]' : ''));

            foreach ($orphanedUnits as $unit) {
                $this->line("  → Unit #{$unit->unit_number} (ID: {$unit->id}) — setting to vacant");

                if (! $dryRun) {
                    // 1. Vacate the unit
                    $unit->update(['status' => 'vacant']);
                    $vacatedCount++;

                    // 2. Deactivate any active tenant left on this unit
                    if ($unit->tenant) {
                        $unit->tenant->update([
                            'status' => 'inactive',
                            'unit_id' => null
                        ]);
                    }
                }
            }
        } else {
            $this->info('Pass 2: No orphaned rented units found.');
        }

        if ($dryRun) {
            $this->warn('DRY RUN complete — no changes were saved.');
        } else {
            $this->info("Done. Expired {$expiredCount} agreement(s), vacated {$vacatedCount} unit(s).");
            Log::info("ExpireAgreements: expired={$expiredCount}, vacated={$vacatedCount}");
        }

        return self::SUCCESS;
    }
}
