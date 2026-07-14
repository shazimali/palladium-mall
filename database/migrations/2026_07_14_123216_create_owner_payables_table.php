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
        Schema::create('owner_payables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('unpaid'); // unpaid, paid
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_payables');
    }
};
