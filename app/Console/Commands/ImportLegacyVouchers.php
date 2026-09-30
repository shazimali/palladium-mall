<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use App\Services\LegacyVoucherLinker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Brings existing Receiving / General Receiving / Paid / Expense voucher rows into the
 * unified Vouchers list by giving each a one-line header. Rows keep their numbers,
 * amounts, dates and accounts, so ledgers are unchanged. Safe to re-run.
 */
class ImportLegacyVouchers extends Command
{
    protected $signature = 'vouchers:import-legacy
                            {--dry-run : Show what would be imported without writing anything}
                            {--undo : Remove all imported headers and unlink their rows}';

    protected $description = 'Add existing vouchers (RV, GRV, PV, expenses) to the unified Vouchers list';

    public function handle(LegacyVoucherLinker $linker): int
    {
        if ($this->option('undo')) {
            return $this->undo();
        }

        $dryRun = (bool) $this->option('dry-run');
        $summary = [];
        $skippedManual = [];
        $noAccount = 0;

        foreach (LegacyVoucherLinker::LINE_MODELS as $class) {
            $table = (new $class)->getTable();

            $class::whereNull('voucher_id')->orderBy('id')->chunkById(200, function ($rows) use ($linker, $dryRun, $table, &$summary, &$skippedManual, &$noAccount) {
                DB::transaction(function () use ($rows, $linker, $dryRun, $table, &$summary, &$skippedManual, &$noAccount) {
                    foreach ($rows as $row) {
                        if (!$row->payment_account_id) {
                            $noAccount++;
                            continue;
                        }

                        $key = $table . '|' . $linker->typeFor($row) . '|' . $linker->sourceFor($row);
                        $summary[$key] = ($summary[$key] ?? 0) + 1;

                        if (!empty($row->manual_voucher_no) && $linker->headerManualNo($row) === null) {
                            $skippedManual[] = $row->voucher_no . ' (' . $row->manual_voucher_no . ')';
                        }

                        if (!$dryRun) {
                            LegacyVoucherLinker::withoutSync(fn() => $linker->wrap($row));
                        }
                    }
                });
            });
        }

        $this->table(['Table', 'Voucher type', 'Source', 'Rows'], collect($summary)
            ->map(fn($count, $key) => [...explode('|', $key), $count])
            ->values()
            ->all());

        $this->line('Total: ' . array_sum($summary) . ($dryRun ? ' (dry run, nothing written)' : ' imported'));

        if ($skippedManual) {
            $this->warn('Manual numbers not copied to the header (placeholder or already used); the rows keep them:');
            $this->line('  ' . implode(', ', $skippedManual));
        }
        if ($noAccount) {
            $this->warn("$noAccount row(s) skipped because they have no payment account.");
        }

        return self::SUCCESS;
    }

    private function undo(): int
    {
        if (!$this->confirm('Remove all imported voucher headers and unlink their rows?')) {
            return self::FAILURE;
        }

        $count = DB::transaction(function () {
            $ids = Voucher::withTrashed()->where('source', '!=', 'form')->pluck('id');

            foreach (LegacyVoucherLinker::LINE_MODELS as $class) {
                DB::table((new $class)->getTable())->whereIn('voucher_id', $ids)->update(['voucher_id' => null, 'line_no' => null]);
            }

            // Hard delete so a later re-import can reuse the same voucher numbers
            return Voucher::withTrashed()->whereIn('id', $ids)->forceDelete();
        });

        $this->info("Removed $count imported header(s).");

        return self::SUCCESS;
    }
}
