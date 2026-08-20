<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add daily settings to report_types
        Schema::table('report_types', function (Blueprint $table) {
            if (!Schema::hasColumn('report_types', 'is_daily')) {
                $table->boolean('is_daily')->default(false)->after('description');
                $table->time('daily_start_time')->default('09:00:00')->after('is_daily');
                $table->time('daily_end_time')->default('20:00:00')->after('daily_start_time');
                $table->boolean('one_per_user_daily')->default(true)->after('daily_end_time');
            }
        });

        // Set cleaning report type as daily by default
        DB::table('report_types')
            ->where('key', 'cleaning')
            ->update([
                'is_daily'            => true,
                'daily_start_time'    => '09:00:00',
                'daily_end_time'      => '20:00:00',
                'one_per_user_daily'  => true,
            ]);

        // 2. Predefined System Remarks per Report Type
        if (!Schema::hasTable('report_type_remarks')) {
            Schema::create('report_type_remarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_type_id')->constrained('report_types')->cascadeOnDelete();
                $table->string('remark');
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed some common default remarks for cleaning & flat inspection
            $cleaning = DB::table('report_types')->where('key', 'cleaning')->first();
            if ($cleaning) {
                DB::table('report_type_remarks')->insert([
                    ['report_type_id' => $cleaning->id, 'remark' => 'Clean & Satisfactory', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $cleaning->id, 'remark' => 'Dusty / Needs Dusting', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $cleaning->id, 'remark' => 'Garbage / Stains Not Cleaned', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $cleaning->id, 'remark' => 'Deep Cleaning Required', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $cleaning->id, 'remark' => 'Not Applicable / Closed', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            $flat = DB::table('report_types')->where('key', 'flat_inspection')->first();
            if ($flat) {
                DB::table('report_type_remarks')->insert([
                    ['report_type_id' => $flat->id, 'remark' => 'Good Condition / Working', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $flat->id, 'remark' => 'Minor Wear & Tear', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $flat->id, 'remark' => 'Damaged / Needs Repair', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $flat->id, 'remark' => 'Missing Item / Fixture', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                    ['report_type_id' => $flat->id, 'remark' => 'Not Applicable', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        }

        // 3. Dynamic Inspection Reports (Multi-type master reports)
        if (!Schema::hasTable('inspection_reports')) {
            Schema::create('inspection_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_type_id')->constrained('report_types')->cascadeOnDelete();
                $table->date('report_date');
                $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('overall_remarks')->nullable();
                $table->string('status')->default('completed');
                $table->timestamps();

                $table->index(['report_type_id', 'report_date'], 'ir_type_date_idx');
            });
        }

        // 4. Dynamic Inspection Report Items per Head
        if (!Schema::hasTable('inspection_report_items')) {
            Schema::create('inspection_report_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_report_id')->constrained('inspection_reports')->cascadeOnDelete();
                $table->foreignId('inspection_head_id')->constrained('inspection_heads')->cascadeOnDelete();
                $table->string('status')->nullable(); // 'yes', 'no', 'na' or 'pass', 'fail', 'na'
                $table->foreignId('report_type_remark_id')->nullable()->constrained('report_type_remarks')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->string('image_path')->nullable();
                $table->timestamps();

                $table->index(['inspection_report_id', 'inspection_head_id'], 'iri_rep_head_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_report_items');
        Schema::dropIfExists('inspection_reports');
        Schema::dropIfExists('report_type_remarks');

        Schema::table('report_types', function (Blueprint $table) {
            if (Schema::hasColumn('report_types', 'is_daily')) {
                $table->dropColumn(['is_daily', 'daily_start_time', 'daily_end_time', 'one_per_user_daily']);
            }
        });
    }
};
