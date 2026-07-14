<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixPrecision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmms:fix-precision';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rounds all fractional payments and vouchers to nearest whole numbers to fix floating-point precision anomalies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting database precision fix...");

        $tables = [
            'agreements'                  => ['monthly_rent', 'maintenance_charge', 'security_deposit', 'fine_per_day'],
            'payments'                    => ['amount', 'amount_paid'],
            'receiving_vouchers'          => ['amount'],
            'receiving_voucher_payments'  => ['amount_allocated'],
            'payment_vouchers'            => ['amount'],
            'expenses'                    => ['amount'],
        ];

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table {$table} does not exist, skipping.");
                continue;
            }
            
            foreach ($columns as $column) {
                $affected = DB::table($table)
                    ->whereRaw("{$column} - FLOOR({$column}) > 0")
                    ->update([
                        $column => DB::raw("ROUND({$column}, 0)")
                    ]);
                    
                $this->line("Updated {$affected} records in table '{$table}' column '{$column}'.");
            }
        }

        $this->info("Database precision fix completed successfully!");
    }
}
