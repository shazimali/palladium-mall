<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->decimal('initial_meter_reading', 15, 2)->nullable()->after('security_deposit');
            $table->decimal('final_meter_reading', 15, 2)->nullable()->after('initial_meter_reading');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['initial_meter_reading', 'final_meter_reading']);
        });
    }
};
