<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_types')) {
            Schema::create('report_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed default report types
            DB::table('report_types')->insert([
                [
                    'name'       => 'Flat Inspection',
                    'key'        => 'flat_inspection',
                    'description'=> 'Inspection heads used for Move-in and Move-out flat checklists',
                    'is_active'  => 1,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name'       => 'Cleaning',
                    'key'        => 'cleaning',
                    'description'=> 'Inspection heads used for daily cleaning inspection reports',
                    'is_active'  => 1,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Alter inspection_heads.type from ENUM to VARCHAR so dynamic types can be saved
        DB::statement("ALTER TABLE inspection_heads MODIFY COLUMN type VARCHAR(100) NOT NULL DEFAULT 'flat_inspection'");
    }

    public function down(): void
    {
        Schema::dropIfExists('report_types');
        DB::statement("ALTER TABLE inspection_heads MODIFY COLUMN type ENUM('flat_inspection', 'cleaning') NOT NULL DEFAULT 'flat_inspection'");
    }
};
