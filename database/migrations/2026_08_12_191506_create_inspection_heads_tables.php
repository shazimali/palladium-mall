<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master list of inspection heads (flat_inspection & cleaning types)
        Schema::create('inspection_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->enum('type', ['flat_inspection', 'cleaning']);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Flat inspection reports (per Agreement, move_in or move_out)
        Schema::create('flat_inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('type', ['move_in', 'move_out']);
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('inspection_member')->nullable(); // freetext inspector name
            $table->foreignId('inspection_person_id')->nullable()->constrained('inspection_persons')->nullOnDelete();
            $table->date('inspected_at')->nullable();
            $table->string('flat_condition')->nullable(); // good / average / poor
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['agreement_id', 'type']);
        });

        // Per-head items of each flat inspection report
        Schema::create('flat_inspection_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flat_inspection_report_id')->constrained('flat_inspection_reports')->cascadeOnDelete();
            $table->foreignId('inspection_head_id')->constrained('inspection_heads')->cascadeOnDelete();
            $table->boolean('status')->nullable();   // true = Pass, false = Fail, null = N/A
            $table->string('image_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Daily cleaning inspection reports (building-wide)
        Schema::create('cleaning_inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('overall_remarks')->nullable();
            $table->timestamps();

            $table->unique('report_date'); // one report per day
        });

        // Per-head items of each cleaning report
        Schema::create('cleaning_inspection_report_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cleaning_inspection_report_id');
            $table->foreign('cleaning_inspection_report_id', 'ciri_report_id_foreign')
                  ->references('id')->on('cleaning_inspection_reports')->cascadeOnDelete();
            $table->foreignId('inspection_head_id')->constrained('inspection_heads')->cascadeOnDelete();
            $table->boolean('status')->nullable();   // true = Clean, false = Not Clean, null = N/A
            $table->string('image_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_inspection_report_items');
        Schema::dropIfExists('cleaning_inspection_reports');
        Schema::dropIfExists('flat_inspection_report_items');
        Schema::dropIfExists('flat_inspection_reports');
        Schema::dropIfExists('inspection_heads');
    }
};
