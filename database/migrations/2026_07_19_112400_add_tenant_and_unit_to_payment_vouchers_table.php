<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('party_id')->constrained('tenants')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('tenant_id')->constrained('units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
