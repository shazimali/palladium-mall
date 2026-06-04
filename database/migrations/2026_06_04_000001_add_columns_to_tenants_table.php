<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns to tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('name');
            $table->date('date_of_birth')->nullable()->after('father_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->string('whatsapp_number')->nullable()->after('phone');
            $table->decimal('monthly_income', 10, 2)->nullable()->after('occupation');
            $table->enum('tenancy_type', ['residential', 'commercial', 'student'])->default('residential')->after('monthly_income');
            $table->integer('adults_count')->default(1)->after('dependents');
            $table->integer('children_count')->default(0)->after('adults_count');
            $table->string('passport_photo')->nullable()->after('cnic_back_image');
        });

        // 2. Update status enum to include 'draft'
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('draft','active','inactive') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'father_name', 'date_of_birth', 'gender', 'marital_status',
                'whatsapp_number', 'monthly_income', 'tenancy_type',
                'adults_count', 'children_count', 'passport_photo',
            ]);
        });

        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active'");
    }
};
