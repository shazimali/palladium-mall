<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number');          // A-101, S-G01
            $table->string('floor')->nullable();     // Floor 1, Ground
            $table->string('block')->nullable();     // Block A, Block B
            $table->enum('type', ['flat', 'shop']);
            $table->enum('status', ['vacant', 'occupied', 'sold'])->default('vacant');
            $table->decimal('area_sqft', 8, 2)->nullable();
            $table->string('elec_meter_id')->nullable();
            $table->string('water_meter_id')->nullable();
            $table->string('gas_meter_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('unit_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};