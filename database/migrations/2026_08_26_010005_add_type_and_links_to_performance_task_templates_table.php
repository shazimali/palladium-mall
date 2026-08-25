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
            $table->string('type', 50)->default('custom')->after('name');
            $table->foreignId('report_type_id')->nullable()->after('type')->constrained('report_types')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->after('report_type_id')->constrained('tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('performance_task_templates', function (Blueprint $table) {
            $table->dropForeign(['report_type_id']);
            $table->dropForeign(['task_id']);
            $table->dropColumn(['type', 'report_type_id', 'task_id']);
        });
    }
};
