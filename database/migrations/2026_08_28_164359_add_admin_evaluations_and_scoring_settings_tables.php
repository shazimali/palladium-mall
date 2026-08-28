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
                if (!Schema::hasColumn('report_types', 'satisfactory_score_pct')) {
                    $table->decimal('satisfactory_score_pct', 5, 2)->default(100.00)->after('is_active');
                }
                if (!Schema::hasColumn('report_types', 'unsatisfactory_score_pct')) {
                    $table->decimal('unsatisfactory_score_pct', 5, 2)->default(0.00)->after('satisfactory_score_pct');
                }
            });
        }

        if (Schema::hasTable('inspection_report_items')) {
            Schema::table('inspection_report_items', function (Blueprint $table) {
                if (!Schema::hasColumn('inspection_report_items', 'admin_rating')) {
                    $table->string('admin_rating', 20)->nullable()->after('remarks');
                }
                if (!Schema::hasColumn('inspection_report_items', 'admin_remarks')) {
                    $table->text('admin_remarks')->nullable()->after('admin_rating');
                }
                if (!Schema::hasColumn('inspection_report_items', 'admin_photo')) {
                    $table->string('admin_photo')->nullable()->after('admin_remarks');
                }
            });
        }

        if (Schema::hasTable('flat_inspection_report_items')) {
            Schema::table('flat_inspection_report_items', function (Blueprint $table) {
                if (!Schema::hasColumn('flat_inspection_report_items', 'admin_rating')) {
                    $table->string('admin_rating', 20)->nullable()->after('remarks');
                }
                if (!Schema::hasColumn('flat_inspection_report_items', 'admin_remarks')) {
                    $table->text('admin_remarks')->nullable()->after('admin_rating');
                }
                if (!Schema::hasColumn('flat_inspection_report_items', 'admin_photo')) {
                    $table->string('admin_photo')->nullable()->after('admin_remarks');
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
                $table->dropColumn(['satisfactory_score_pct', 'unsatisfactory_score_pct']);
            });
        }

        if (Schema::hasTable('inspection_report_items')) {
            Schema::table('inspection_report_items', function (Blueprint $table) {
                $table->dropColumn(['admin_rating', 'admin_remarks', 'admin_photo']);
            });
        }

        if (Schema::hasTable('flat_inspection_report_items')) {
            Schema::table('flat_inspection_report_items', function (Blueprint $table) {
                $table->dropColumn(['admin_rating', 'admin_remarks', 'admin_photo']);
            });
        }
    }
};

