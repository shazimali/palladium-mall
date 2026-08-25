<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_reading_vouchers', function (Blueprint $table) {
            $table->string('available')->nullable()->after('units_consumed');
        });
    }

    public function down(): void
    {
        Schema::table('meter_reading_vouchers', function (Blueprint $table) {
            $table->dropColumn('available');
        });
    }
};
