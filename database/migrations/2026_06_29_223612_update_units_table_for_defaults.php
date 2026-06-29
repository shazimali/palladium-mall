<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('self_maintenance_charge', 'default_maintenance_charge');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->decimal('default_monthly_rent', 10, 2)->nullable()->after('default_maintenance_charge');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('default_monthly_rent');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('default_maintenance_charge', 'self_maintenance_charge');
        });
    }
};
