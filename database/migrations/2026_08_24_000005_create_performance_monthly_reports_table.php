<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');  // 1–12
            $table->unsignedSmallInteger('year');

            // Attendance
            $table->unsignedInteger('working_days')->default(0);
            $table->unsignedInteger('days_present')->default(0);
            $table->unsignedInteger('days_absent')->default(0);

            // Performance
            $table->decimal('total_max_points', 12, 2)->default(0);
            $table->decimal('total_earned_points', 12, 2)->default(0);
            $table->decimal('performance_percentage', 5, 2)->default(0);
            $table->string('grade')->nullable(); // Excellent / Good / Average / Poor

            // Salary snapshots
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('fuel_allowance', 12, 2)->default(0);
            $table->decimal('attendance_incentive', 12, 2)->default(0);
            $table->decimal('collection_incentive_pct', 5, 2)->default(0);
            $table->decimal('collection_incentive_amt', 12, 2)->default(0);

            // Salary totals
            $table->decimal('other_works_amount', 12, 2)->default(0);
            $table->decimal('total_basic', 12, 2)->default(0);
            $table->decimal('final_salary', 12, 2)->default(0);

            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_monthly_reports');
    }
};
