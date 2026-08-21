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
        Schema::table('flat_inspection_report_items', function (Blueprint $table) {
            $table->foreignId('report_type_remark_id')->nullable()->after('status')->constrained('report_type_remarks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_inspection_report_items', function (Blueprint $table) {
            $table->dropForeign(['report_type_remark_id']);
            $table->dropColumn('report_type_remark_id');
        });
    }
};
