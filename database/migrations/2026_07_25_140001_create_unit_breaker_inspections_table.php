<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_breaker_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->nullOnDelete();
            $table->enum('breaker_status', ['on', 'off'])->default('off');
            $table->decimal('meter_reading', 15, 2)->default(0);
            $table->string('meter_image')->nullable();
            $table->string('inspection_officer_name')->nullable();
            $table->text('officer_statement')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_breaker_inspections');
    }
};
