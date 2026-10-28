<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'receiving_vouchers',
        'general_receiving_vouchers',
        'payment_vouchers',
        'expenses',
    ];

    /**
     * Additive only: existing rows keep voucher_id = NULL.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('voucher_id')->nullable()->after('id')->constrained('vouchers')->nullOnDelete();
                $table->unsignedSmallInteger('line_no')->nullable()->after('voucher_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('voucher_id');
                $table->dropColumn('line_no');
            });
        }
    }
};
