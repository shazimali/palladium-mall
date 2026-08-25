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
        Schema::table('performance_task_templates', function (Blueprint $table) {
            $table->boolean('is_daily')->default(true)->after('monthly_points');
            $table->unsignedSmallInteger('target_count')->nullable()->default(1)->after('is_daily');
        });
    }

    public function down(): void
    {
        Schema::table('performance_task_templates', function (Blueprint $table) {
            $table->dropColumn(['is_daily', 'target_count']);
        });
    }
};
