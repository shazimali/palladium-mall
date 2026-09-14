<?php

namespace App\Console\Commands;

use App\Models\Agreement;
use App\Support\LegacyDraftActivator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivateEligibleDraftAgreements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agreements:activate-eligible-drafts
                            {--dry-run : Show what would be activated without saving}';

    /**
     * The console command description.
     */
    protected $description = 'One-time backfill: activate pre-existing draft agreements that already have all the data the merged Step 1 now requires (unit, dates, rent, deposit, due day, fine/day).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $eligible = Agreement::where('status', 'draft')
            ->whereNotNull('unit_id')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereNotNull('monthly_rent')
            ->whereNotNull('security_deposit')
            ->whereNotNull('payment_due_day')
            ->whereNotNull('fine_per_day')
            ->with(['tenant', 'unit'])
            ->get();

        if ($eligible->isEmpty()) {
            $this->info('No eligible draft agreements found.');
            return self::SUCCESS;
        }

        $this->info("Found {$eligible->count()} draft agreement(s) with all required fields." . ($dryRun ? ' [DRY RUN]' : ''));

        $activated = 0;
        $skipped = 0;

        foreach ($eligible as $agreement) {
            $tenantName = optional($agreement->tenant)->name ?? 'Unknown';
            $unitNumber = optional($agreement->unit)->unit_number ?? 'N/A';

            if (!$agreement->tenant) {
                $this->warn("  → Agreement #{$agreement->id} | Tenant missing — skipping.");
                $skipped++;
                continue;
            }

            if (LegacyDraftActivator::hasOtherActiveAgreement($agreement)) {
                $this->warn("  → Agreement #{$agreement->id} | Tenant: {$tenantName} | Unit: {$unitNumber} — tenant already has another active agreement, skipping (needs manual review).");
                $skipped++;
                continue;
            }

            $this->line("  → Agreement #{$agreement->id} | Tenant: {$tenantName} | Unit: {$unitNumber} — activating");

            if (!$dryRun) {
                LegacyDraftActivator::activate($agreement);
            }
            $activated++;
        }

        if ($dryRun) {
            $this->warn("DRY RUN complete — would activate {$activated} agreement(s), skip {$skipped}. No changes were saved.");
        } else {
            $this->info("Done. Activated {$activated} agreement(s), skipped {$skipped}.");
            Log::info("ActivateEligibleDraftAgreements: activated={$activated}, skipped={$skipped}");
        }

        return self::SUCCESS;
    }
}
