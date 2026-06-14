<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_partners', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('name');
            $table->string('gender')->nullable()->after('cnic');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('whatsapp_number', 20)->nullable()->after('phone');
            $table->string('email')->nullable()->after('whatsapp_number');
            $table->string('address', 500)->nullable()->after('email');
            $table->string('occupation')->nullable()->after('address');
            $table->decimal('monthly_income', 15, 2)->nullable()->after('occupation');
            $table->string('passport_photo')->nullable()->after('monthly_income');
            $table->string('cnic_front_image')->nullable()->after('passport_photo');
            $table->string('cnic_back_image')->nullable()->after('cnic_front_image');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_partners', function (Blueprint $table) {
            $table->dropColumn([
                'father_name',
                'gender',
                'marital_status',
                'whatsapp_number',
                'email',
                'address',
                'occupation',
                'monthly_income',
                'passport_photo',
                'cnic_front_image',
                'cnic_back_image',
            ]);
        });
    }
};
