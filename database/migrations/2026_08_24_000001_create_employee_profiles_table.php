<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_code')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->date('joined_at')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('fuel_allowance', 12, 2)->default(0);
            $table->decimal('attendance_incentive', 12, 2)->default(0);
            $table->decimal('collection_incentive_pct', 5, 2)->default(0)->comment('Percentage e.g. 5.00 means 5%');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
