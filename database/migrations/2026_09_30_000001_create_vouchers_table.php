<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header table for multi-line Cash/Bank Received/Paid vouchers. Each line is
     * stored as a normal row in the existing voucher tables (linked by voucher_id),
     * so ledgers keep reading those tables unchanged.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique(); // plain 6-digit series, e.g. 000001
            $table->string('type'); // cash_received, cash_paid, bank_received, bank_paid
            $table->string('manual_voucher_no')->nullable()->unique();
            $table->date('date');
            $table->foreignId('payment_account_id')->constrained('payment_accounts');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('reference')->nullable();
            $table->text('narration')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
