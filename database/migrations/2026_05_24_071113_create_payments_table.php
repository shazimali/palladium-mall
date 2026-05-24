<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['rent', 'maintenance', 'fine', 'other']);
            $table->date('month');
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'other'])->nullable();
            $table->string('reference')->nullable();
            $table->string('receipt')->nullable();
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // One record per tenant per type per month
            $table->unique(['tenant_id', 'type', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};