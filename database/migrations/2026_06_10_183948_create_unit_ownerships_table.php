<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_ownerships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnDelete();

            $table->foreignId('landlord_id')
                ->constrained('landlords')
                ->cascadeOnDelete();

            $table->boolean('is_current')->default(true)->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();   // null = still current owner

            // ── Nominee ──────────────────────────────────────────────────
            $table->string('nominee_name')->nullable();
            $table->enum('nominee_relation_type', ['son_of', 'daughter_of', 'wife_of'])->nullable();
            $table->string('nominee_relation_name')->nullable(); // father / husband name

            // ── Financial ────────────────────────────────────────────────
            $table->decimal('total_amount',    12, 2)->nullable();
            $table->decimal('received_amount', 12, 2)->nullable();
            $table->decimal('credit_amount',   12, 2)->nullable(); // auto = total - received
            $table->string('received_from')->nullable();

            // ── Office record ────────────────────────────────────────────
            $table->string('file_no')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('received_by')->nullable();
            $table->date('approved_date')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Composite index — one current owner per unit
            $table->index(['unit_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_ownerships');
    }
};
