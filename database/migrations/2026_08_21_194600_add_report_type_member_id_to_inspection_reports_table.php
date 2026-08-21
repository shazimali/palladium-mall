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
        Schema::table('inspection_reports', function (Blueprint $table) {
            $table->foreignId('report_type_member_id')->nullable()->after('report_type_id')->constrained('report_type_members')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_reports', function (Blueprint $table) {
            $table->dropForeign(['report_type_member_id']);
            $table->dropColumn('report_type_member_id');
        });
    }
};
