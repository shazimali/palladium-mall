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
        Schema::table('general_receiving_vouchers', function (Blueprint $table) {
            // Make party_id nullable (was required before, now optional when receiving from landlord/account)
            $table->foreignId('landlord_id')->nullable()->after('party_id')->constrained('landlords')->nullOnDelete();
        });

        // Also ensure party_id is nullable if not already
        Schema::table('general_receiving_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('party_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_receiving_vouchers', function (Blueprint $table) {
            $table->dropForeign(['landlord_id']);
            $table->dropColumn('landlord_id');
            // Revert party_id to non-nullable
            $table->unsignedBigInteger('party_id')->nullable(false)->change();
        });
    }
};
