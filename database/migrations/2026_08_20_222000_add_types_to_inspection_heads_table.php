<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_heads', function (Blueprint $table) {
            if (!Schema::hasColumn('inspection_heads', 'types')) {
                $table->json('types')->nullable()->after('type');
            }
        });

        // Backfill types array from existing type column
        $heads = DB::table('inspection_heads')->get();
        foreach ($heads as $head) {
            if (!empty($head->type) && empty($head->types)) {
                DB::table('inspection_heads')
                    ->where('id', $head->id)
                    ->update(['types' => json_encode([$head->type])]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('inspection_heads', function (Blueprint $table) {
            if (Schema::hasColumn('inspection_heads', 'types')) {
                $table->dropColumn('types');
            }
        });
    }
};
