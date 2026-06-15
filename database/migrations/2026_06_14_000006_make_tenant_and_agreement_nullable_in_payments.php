<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['agreement_id']);
            
            // Drop unique constraint
            $table->dropUnique(['tenant_id', 'type', 'month']);
        });

        Schema::table('payments', function (Blueprint $table) {
            // Make columns nullable
            $table->foreignId('tenant_id')->nullable()->change();
            $table->foreignId('agreement_id')->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            // Re-add foreign keys with cascade on delete (but they can be null)
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('agreement_id')->references('id')->on('agreements')->cascadeOnDelete();

            // Add new unique constraint per unit/type/month instead of tenant/type/month
            $table->unique(['unit_id', 'type', 'month']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['agreement_id']);
            $table->dropUnique(['unit_id', 'type', 'month']);
        });

        // First clean up any null values or duplicate entries before restoring old constraints
        // (Usually, in down(), we might have nulls or duplicates, but this is a database state rollback)
        
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
            $table->foreignId('agreement_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('agreement_id')->references('id')->on('agreements')->cascadeOnDelete();
            $table->unique(['tenant_id', 'type', 'month']);
        });
    }
};
