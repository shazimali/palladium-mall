<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('notes')->comment('Passport-size photo path');
        });
    }

    public function down(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
