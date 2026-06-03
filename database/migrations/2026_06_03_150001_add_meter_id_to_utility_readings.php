<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->foreignId('meter_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('meters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropForeign('utility_readings_meter_id_foreign');
            $table->dropColumn('meter_id');
        });
    }
};
