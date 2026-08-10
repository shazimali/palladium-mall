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
        Schema::create('note_pads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->date('date')->nullable();
            $table->string('color', 50)->default('default'); // default, yellow, blue, green, pink, purple, orange
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_checklist')->default(false);
            $table->string('status', 30)->default('pending'); // pending, completed
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_pads');
    }
};
