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
        Schema::create('post_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_schedule_head_id')->constrained('post_schedule_heads')->cascadeOnDelete();
            $table->string('day_of_week'); // monday, tuesday, wednesday, thursday, friday, saturday, sunday
            $table->string('employee_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable(); // e.g. Main Entrance Gate 1, Basement Parking B1, Food Court Level 3
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('task_title');
            $table->text('duties')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['day_of_week', 'post_schedule_head_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_schedules');
    }
};
