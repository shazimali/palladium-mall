<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create meters table
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['electricity', 'water', 'gas']);
            $table->string('meter_ref_no', 100);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // One meter per type per unit
            $table->unique(['unit_id', 'type']);
        });

        // 2. Drop old plain-text meter columns from units
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['elec_meter_id', 'water_meter_id', 'gas_meter_id']);
        });
    }

    public function down(): void
    {
        // Re-add plain-text columns
        Schema::table('units', function (Blueprint $table) {
            $table->string('elec_meter_id')->nullable();
            $table->string('water_meter_id')->nullable();
            $table->string('gas_meter_id')->nullable();
        });

        Schema::dropIfExists('meters');
    }
};
