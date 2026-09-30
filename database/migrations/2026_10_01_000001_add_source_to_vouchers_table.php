<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where a voucher header came from:
     *  form        - created in the new voucher form (numbered 000001, 000002, ...)
     *  legacy      - wraps an existing voucher row, keeps its old number (PM-RV-00012)
     *  stock_entry - wraps an expense created by Stock Entries (edited there)
     *  move_out    - wraps a receipt created by Move-out (edited there)
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('source', 20)->default('form')->after('type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};
