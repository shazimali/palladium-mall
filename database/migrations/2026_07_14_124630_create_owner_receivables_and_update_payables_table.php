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
        Schema::dropIfExists('owner_payables');

        Schema::create('owner_payables', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->foreignId('payment_account_id')->constrained('payment_accounts');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('owner_receivables', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->foreignId('payment_account_id')->constrained('payment_accounts');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_receivables');
        Schema::dropIfExists('owner_payables');
    }
};
