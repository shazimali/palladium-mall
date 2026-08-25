<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_reading_vouchers', function (Blueprint $table) {
            $table->decimal('previous_reading', 12, 2)->nullable()->default(0)->after('meter_ref_no');
            $table->decimal('units_consumed', 12, 2)->nullable()->default(0)->after('current_reading');
        });
    }

    public function down(): void
    {
        Schema::table('meter_reading_vouchers', function (Blueprint $table) {
            $table->dropColumn(['previous_reading', 'units_consumed']);
        });
    }
};
