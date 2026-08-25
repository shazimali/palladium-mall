<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('creator_remarks')->nullable()->after('description');
            $table->text('assignee_remarks')->nullable()->after('creator_remarks');
        });

        // Copy any existing data from remarks to creator_remarks
        if (Schema::hasColumn('tasks', 'remarks')) {
            DB::statement('UPDATE tasks SET creator_remarks = remarks WHERE remarks IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['creator_remarks', 'assignee_remarks']);
        });
    }
};
