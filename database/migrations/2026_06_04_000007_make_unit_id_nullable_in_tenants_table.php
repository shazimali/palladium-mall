<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->change();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // First, check if there are any null unit_ids. If so, they must be assigned or deleted before reverting.
        // But in down(), we'll assume rollback is for clean states.
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();
        });
    }
};
