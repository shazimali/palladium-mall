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
        Schema::create('receiving_voucher_landlord_payables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landlord_payable_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_allocated', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receiving_voucher_landlord_payables');
    }
};
