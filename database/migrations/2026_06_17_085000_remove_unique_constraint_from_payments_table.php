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
        Schema::table('payments', function (Blueprint $table) {
            // First add the normal index so the foreign key has a covering index
            $table->index(['unit_id', 'type', 'month']);
        });

        Schema::table('payments', function (Blueprint $table) {
            // Now we can safely drop the unique index
            $table->dropUnique(['unit_id', 'type', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add unique index back
            $table->unique(['unit_id', 'type', 'month']);
        });

        Schema::table('payments', function (Blueprint $table) {
            // Drop regular index
            $table->dropIndex(['unit_id', 'type', 'month']);
        });
    }
};
