<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add file_no to units table
        Schema::table('units', function (Blueprint $table) {
            $table->string('file_no')->nullable()->unique()->after('status')->comment('Unique office file number');
        });

        // 2. Drop file_no from unit_ownerships table
        Schema::table('unit_ownerships', function (Blueprint $table) {
            $table->dropColumn('file_no');
        });
    }

    public function down(): void
    {
        // 1. Re-add file_no to unit_ownerships table
        Schema::table('unit_ownerships', function (Blueprint $table) {
            $table->string('file_no')->nullable()->after('received_from');
        });

        // 2. Drop file_no from units table
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('file_no');
        });
    }
};
