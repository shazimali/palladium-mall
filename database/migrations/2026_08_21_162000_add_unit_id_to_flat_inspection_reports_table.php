<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flat_inspection_reports', function (Blueprint $table) {
            $table->string('type', 50)->default('move_in')->change();
            $table->foreignId('unit_id')->nullable()->after('id')->constrained('units')->nullOnDelete();
            $table->unsignedBigInteger('agreement_id')->nullable()->change();
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
        });

        // Backfill unit_id from existing agreements
        DB::statement("
            UPDATE flat_inspection_reports fir
            INNER JOIN agreements a ON fir.agreement_id = a.id
            SET fir.unit_id = a.unit_id
            WHERE fir.unit_id IS NULL AND a.unit_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_inspection_reports', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
