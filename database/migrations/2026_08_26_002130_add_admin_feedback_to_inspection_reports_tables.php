<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inspection_reports')) {
            Schema::table('inspection_reports', function (Blueprint $table) {
                $table->text('admin_remarks')->nullable()->after('overall_remarks');
                $table->string('admin_rating', 20)->nullable()->after('admin_remarks'); // good | bad
                $table->string('admin_photo')->nullable()->after('admin_rating');
            });
        }

        if (Schema::hasTable('flat_inspection_reports')) {
            Schema::table('flat_inspection_reports', function (Blueprint $table) {
                $table->text('admin_remarks')->nullable()->after('remarks');
                $table->string('admin_rating', 20)->nullable()->after('admin_remarks'); // good | bad
                $table->string('admin_photo')->nullable()->after('admin_rating');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inspection_reports')) {
            Schema::table('inspection_reports', function (Blueprint $table) {
                $table->dropColumn(['admin_remarks', 'admin_rating', 'admin_photo']);
            });
        }

        if (Schema::hasTable('flat_inspection_reports')) {
            Schema::table('flat_inspection_reports', function (Blueprint $table) {
                $table->dropColumn(['admin_remarks', 'admin_rating', 'admin_photo']);
            });
        }
    }
};
