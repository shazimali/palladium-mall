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
        Schema::create('inspection_persons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('move_in_checklists', function (Blueprint $table) {
            $table->foreignId('inspection_person_id')
                ->nullable()
                ->after('agreement_id')
                ->constrained('inspection_persons')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('move_in_checklists', function (Blueprint $table) {
            $table->dropForeign(['inspection_person_id']);
            $table->dropColumn('inspection_person_id');
        });

        Schema::dropIfExists('inspection_persons');
    }
};
