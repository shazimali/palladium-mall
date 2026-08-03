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
        Schema::create('jv_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->foreignId('expense_head_id')->constrained('expense_heads')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jv_vouchers');
    }
};
