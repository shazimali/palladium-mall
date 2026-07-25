<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->enum('breaker_status', ['on', 'off'])->default('off')->after('status');
        });

        // Populate existing rented/occupied units with breaker_status = 'on'
        DB::table('units')
            ->where('status', 'rented')
            ->orWhereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('other_tenants')
                    ->whereColumn('other_tenants.unit_id', 'units.id');
            })
            ->update(['breaker_status' => 'on']);
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('breaker_status');
        });
    }
};
