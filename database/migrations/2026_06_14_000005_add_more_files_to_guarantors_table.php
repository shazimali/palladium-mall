<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guarantors', function (Blueprint $table) {
            $table->string('cnic_front')->nullable()->after('cnic_image');
            $table->string('cnic_back')->nullable()->after('cnic_front');
            $table->string('photo')->nullable()->after('cnic_back');
            $table->string('visiting_card')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('guarantors', function (Blueprint $table) {
            $table->dropColumn(['cnic_front', 'cnic_back', 'photo', 'visiting_card']);
        });
    }
};
