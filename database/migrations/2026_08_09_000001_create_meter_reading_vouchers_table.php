<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meter_reading_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->date('date');
            $table->string('meter_ref_no')->nullable();
            $table->decimal('current_reading', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('meter_image')->nullable();
            $table->enum('status', ['paid', 'unpaid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_reading_vouchers');
    }
};
