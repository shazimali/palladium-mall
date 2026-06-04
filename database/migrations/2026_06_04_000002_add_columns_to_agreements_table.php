<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_due_day')->default(5)->after('security_deposit');
            $table->unsignedTinyInteger('notice_period_months')->default(1)->after('grace_period_days');
        });
    }

    public function down(): void
    {
        Schema::table('agreements', function (Blueprint $table) {
            $table->dropColumn(['payment_due_day', 'notice_period_months']);
        });
    }
};
