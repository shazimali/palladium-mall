<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('creator_rating', 20)->nullable()->after('creator_remarks');
        });

        // If creator_remarks was set to 'good' or 'bad', migrate it to creator_rating
        DB::statement("UPDATE tasks SET creator_rating = creator_remarks, creator_remarks = NULL WHERE creator_remarks IN ('good', 'bad')");
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('creator_rating');
        });
    }
};
