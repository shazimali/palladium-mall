<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create floors, blocks, and areas tables
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Add foreign keys to units table (initially nullable)
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable()->after('unit_number')->constrained('floors')->nullOnDelete();
            $table->foreignId('block_id')->nullable()->after('floor_id')->constrained('blocks')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('block_id')->constrained('areas')->nullOnDelete();
        });

        // 3. Migrate existing floor/block string values to the new tables
        $existingUnits = DB::table('units')->get();

        // Migrate Floors
        $floors = $existingUnits->pluck('floor')->filter()->unique();
        foreach ($floors as $floorName) {
            DB::table('floors')->insertOrIgnore([
                'name' => $floorName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Migrate Blocks
        $blocks = $existingUnits->pluck('block')->filter()->unique();
        foreach ($blocks as $blockName) {
            DB::table('blocks')->insertOrIgnore([
                'name' => $blockName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Map units to their new floor_id and block_id
        foreach ($existingUnits as $unit) {
            $floorId = null;
            $blockId = null;

            if ($unit->floor) {
                $floorId = DB::table('floors')->where('name', $unit->floor)->value('id');
            }

            if ($unit->block) {
                $blockId = DB::table('blocks')->where('name', $unit->block)->value('id');
            }

            DB::table('units')->where('id', $unit->id)->update([
                'floor_id' => $floorId,
                'block_id' => $blockId,
            ]);
        }

        // 4. Drop the old string columns
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['floor', 'block']);
        });
    }

    public function down(): void
    {
        // 1. Re-add old columns to units table
        Schema::table('units', function (Blueprint $table) {
            $table->string('floor')->nullable()->after('unit_number');
            $table->string('block')->nullable()->after('floor');
        });

        // 2. Restore string values from relationships
        $units = DB::table('units')->get();
        foreach ($units as $unit) {
            $floorName = null;
            $blockName = null;

            if ($unit->floor_id) {
                $floorName = DB::table('floors')->where('id', $unit->floor_id)->value('name');
            }

            if ($unit->block_id) {
                $blockName = DB::table('blocks')->where('id', $unit->block_id)->value('name');
            }

            DB::table('units')->where('id', $unit->id)->update([
                'floor' => $floorName,
                'block' => $blockName,
            ]);
        }

        // 3. Drop foreign keys and ID columns
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign('units_floor_id_foreign');
            $table->dropForeign('units_block_id_foreign');
            $table->dropForeign('units_area_id_foreign');
            $table->dropColumn(['floor_id', 'block_id', 'area_id']);
        });

        // 4. Drop the created tables
        Schema::dropIfExists('areas');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('floors');
    }
};
