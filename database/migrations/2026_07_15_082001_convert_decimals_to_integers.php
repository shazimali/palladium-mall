<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Round all values first to avoid data loss on conversion
        $tablesWithCols = [
            'units' => ['default_maintenance_charge', 'default_monthly_rent'],
            'agreements' => ['monthly_rent', 'maintenance_charge', 'security_deposit', 'fine_per_day'],
            'payments' => ['amount', 'amount_paid'],
            'receiving_vouchers' => ['amount'],
            'receiving_voucher_payments' => ['amount_allocated'],
            'payment_vouchers' => ['amount'],
            'expenses' => ['amount'],
            'landlord_payables' => ['amount', 'amount_paid'],
            'other_tenants' => ['maintenance_charge'],
            'receiving_voucher_landlord_payables' => ['amount_allocated'],
            'party_dues' => ['amount'],
            'payment_accounts' => ['opening_balance'],
            'general_receiving_vouchers' => ['amount'],
            'unit_ownerships' => ['total_amount', 'received_amount', 'credit_amount'],
            'tenants' => ['monthly_income'],
            'tenant_partners' => ['monthly_income'],
            'move_in_checklists' => ['deposit_deduction'],
            'owner_payables' => ['amount'],
            'owner_receivables' => ['amount'],
        ];

        foreach ($tablesWithCols as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::statement("UPDATE `{$table}` SET `{$column}` = ROUND(`{$column}`, 0) WHERE `{$column}` IS NOT NULL");
                    }
                }
            }
        }

        // 2. Change column types to BigInteger
        Schema::table('units', function (Blueprint $table) {
            $table->bigInteger('default_maintenance_charge')->nullable()->change();
            $table->bigInteger('default_monthly_rent')->nullable()->change();
        });

        Schema::table('agreements', function (Blueprint $table) {
            $table->bigInteger('monthly_rent')->nullable()->change();
            $table->bigInteger('maintenance_charge')->default(0)->change();
            $table->bigInteger('security_deposit')->nullable()->change();
            $table->bigInteger('fine_per_day')->default(0)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('amount_paid')->default(0)->change();
        });

        Schema::table('receiving_vouchers', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        Schema::table('receiving_voucher_payments', function (Blueprint $table) {
            $table->bigInteger('amount_allocated')->change();
        });

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        Schema::table('landlord_payables', function (Blueprint $table) {
            $table->bigInteger('amount')->default(0)->change();
            $table->bigInteger('amount_paid')->default(0)->change();
        });

        Schema::table('other_tenants', function (Blueprint $table) {
            $table->bigInteger('maintenance_charge')->nullable()->change();
        });

        Schema::table('receiving_voucher_landlord_payables', function (Blueprint $table) {
            $table->bigInteger('amount_allocated')->default(0)->change();
        });

        Schema::table('party_dues', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->bigInteger('opening_balance')->default(0)->change();
        });

        Schema::table('general_receiving_vouchers', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        Schema::table('unit_ownerships', function (Blueprint $table) {
            $table->bigInteger('total_amount')->nullable()->change();
            $table->bigInteger('received_amount')->nullable()->change();
            $table->bigInteger('credit_amount')->nullable()->change();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->bigInteger('monthly_income')->nullable()->change();
        });

        Schema::table('tenant_partners', function (Blueprint $table) {
            $table->bigInteger('monthly_income')->nullable()->change();
        });

        Schema::table('move_in_checklists', function (Blueprint $table) {
            $table->bigInteger('deposit_deduction')->default(0)->change();
        });

        if (Schema::hasTable('owner_payables')) {
            Schema::table('owner_payables', function (Blueprint $table) {
                $table->bigInteger('amount')->change();
            });
        }

        if (Schema::hasTable('owner_receivables')) {
            Schema::table('owner_receivables', function (Blueprint $table) {
                $table->bigInteger('amount')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->decimal('default_maintenance_charge', 10, 2)->nullable()->change();
            $table->decimal('default_monthly_rent', 10, 2)->nullable()->change();
        });

        Schema::table('agreements', function (Blueprint $table) {
            $table->decimal('monthly_rent', 10, 2)->nullable()->change();
            $table->decimal('maintenance_charge', 10, 2)->default(0)->change();
            $table->decimal('security_deposit', 10, 2)->nullable()->change();
            $table->decimal('fine_per_day', 8, 2)->default(0)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
            $table->decimal('amount_paid', 10, 2)->default(0)->change();
        });

        Schema::table('receiving_vouchers', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('receiving_voucher_payments', function (Blueprint $table) {
            $table->decimal('amount_allocated', 15, 2)->change();
        });

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('landlord_payables', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->default(0)->change();
            $table->decimal('amount_paid', 12, 2)->default(0)->change();
        });

        Schema::table('other_tenants', function (Blueprint $table) {
            $table->decimal('maintenance_charge', 10, 2)->nullable()->change();
        });

        Schema::table('receiving_voucher_landlord_payables', function (Blueprint $table) {
            $table->decimal('amount_allocated', 12, 2)->default(0)->change();
        });

        Schema::table('party_dues', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->decimal('opening_balance', 14, 2)->default(0.00)->change();
        });

        Schema::table('general_receiving_vouchers', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('unit_ownerships', function (Blueprint $table) {
            $table->decimal('total_amount', 12, 2)->nullable()->change();
            $table->decimal('received_amount', 12, 2)->nullable()->change();
            $table->decimal('credit_amount', 12, 2)->nullable()->change();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('monthly_income', 10, 2)->nullable()->change();
        });

        Schema::table('tenant_partners', function (Blueprint $table) {
            $table->decimal('monthly_income', 15, 2)->nullable()->change();
        });

        Schema::table('move_in_checklists', function (Blueprint $table) {
            $table->decimal('deposit_deduction', 10, 2)->default(0)->change();
        });

        if (Schema::hasTable('owner_payables')) {
            Schema::table('owner_payables', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)->change();
            });
        }

        if (Schema::hasTable('owner_receivables')) {
            Schema::table('owner_receivables', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)->change();
            });
        }
    }
};
