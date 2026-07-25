<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_breaker_inspections', function (Blueprint $table) {
            $table->foreignId('inspection_person_id')->nullable()->after('agreement_id')->constrained('inspection_persons')->nullOnDelete();
            $table->string('signed_inspection_doc')->nullable()->after('officer_statement');
        });
    }

    public function down(): void
    {
        Schema::table('unit_breaker_inspections', function (Blueprint $table) {
            $table->dropForeign(['inspection_person_id']);
            $table->dropColumn(['inspection_person_id', 'signed_inspection_doc']);
        });
    }
};
