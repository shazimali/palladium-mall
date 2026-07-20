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
            $table->unsignedBigInteger('party_id')->nullable()->change();
            $table->string('received_from_type')->default('party')->after('amount');
            $table->foreignId('from_payment_account_id')->nullable()->after('payment_account_id')->constrained('payment_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_receiving_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('party_id')->nullable(false)->change();
            $table->dropForeign(['from_payment_account_id']);
            $table->dropColumn(['received_from_type', 'from_payment_account_id']);
        });
    }
};
