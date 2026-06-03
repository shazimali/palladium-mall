<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            $table->string('meter_consumer_id', 100)->nullable()->after('meter_ref_no');
            $table->string('meter_image')->nullable()->after('meter_consumer_id'); // stored file path
        });
    }

    public function down(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            $table->dropColumn(['meter_consumer_id', 'meter_image']);
        });
    }
};
