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
        if (Schema::hasTable('report_types')) {
            Schema::table('report_types', function (Blueprint $table) {
                if (!Schema::hasColumn('report_types', 'satisfactory_threshold_pct')) {
                    $table->decimal('satisfactory_threshold_pct', 5, 2)->default(50.00)->after('is_active');
                }
                if (!Schema::hasColumn('report_types', 'below_threshold_score_pct')) {
                    $table->decimal('below_threshold_score_pct', 5, 2)->default(50.00)->after('satisfactory_threshold_pct');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('report_types')) {
            Schema::table('report_types', function (Blueprint $table) {
                $table->dropColumn(['satisfactory_threshold_pct', 'below_threshold_score_pct']);
            });
        }
    }
};
