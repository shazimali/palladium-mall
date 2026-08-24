<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('template_id')
                ->constrained('performance_task_templates')
                ->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_done')->default(false);
            $table->decimal('points_earned', 10, 2)->default(0)
                ->comment('Auto-calculated: monthly_points / days_in_month when is_done=true');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'template_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_daily_entries');
    }
};
