<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meter_reading_vouchers', function (Blueprint $table) {
            $table->date('bill_generate_date')->nullable()->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('meter_reading_vouchers', function (Blueprint $table) {
            $table->dropColumn('bill_generate_date');
        });
    }
};
