<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_no')->nullable()->unique()->after('hash');
        });

        // Backfill existing payments
        \DB::table('payments')->orderBy('id')->chunk(100, function ($payments) {
            foreach ($payments as $payment) {
                \DB::table('payments')
                    ->where('id', $payment->id)
                    ->update([
                        'receipt_no' => 'PM-PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT)
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('receipt_no');
        });
    }
};
