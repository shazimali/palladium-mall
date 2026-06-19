<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_tenants', function (Blueprint $table) {
            $table->decimal('maintenance_charge', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('other_tenants', function (Blueprint $table) {
            $table->dropColumn('maintenance_charge');
        });
    }
};
