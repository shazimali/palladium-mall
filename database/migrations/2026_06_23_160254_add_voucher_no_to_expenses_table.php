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
        // 1. Add column as nullable initially so we can populate existing rows
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('voucher_no')->nullable()->after('id');
        });

        // 2. Populate existing rows sequentially
        $expenses = DB::table('expenses')->orderBy('id')->get();
        foreach ($expenses as $e) {
            $voucherNo = 'PM-EV-' . str_pad($e->id, 5, '0', STR_PAD_LEFT);
            DB::table('expenses')->where('id', $e->id)->update([
                'voucher_no' => $voucherNo
            ]);
        }

        // 3. Make column unique and non-nullable
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('voucher_no')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('voucher_no');
        });
    }
};
