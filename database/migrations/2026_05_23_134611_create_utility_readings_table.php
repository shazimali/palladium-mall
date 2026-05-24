<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['electricity', 'water', 'gas']);
            $table->date('month');                              // stored as 2026-05-01
            $table->decimal('previous_reading', 10, 2)->default(0);
            $table->decimal('current_reading', 10, 2);
            $table->decimal('units_consumed', 10, 2);          // auto-calculated
            $table->decimal('rate_per_unit', 8, 2)->default(0);
            $table->decimal('bill_amount', 10, 2);             // admin can override
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->string('bill_proof')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // One reading per unit per type per month
            $table->unique(['unit_id', 'type', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};