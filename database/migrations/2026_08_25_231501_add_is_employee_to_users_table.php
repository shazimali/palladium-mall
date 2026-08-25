<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_employee')->default(false)->after('is_active')->index();
        });

        // Backfill is_employee = true for existing users with employee profiles
        if (Schema::hasTable('employee_profiles')) {
            $employeeUserIds = DB::table('employee_profiles')->pluck('user_id')->toArray();
            if (!empty($employeeUserIds)) {
                DB::table('users')->whereIn('id', $employeeUserIds)->update(['is_employee' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_employee');
        });
    }
};
