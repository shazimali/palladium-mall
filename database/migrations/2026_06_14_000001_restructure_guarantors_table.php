<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guarantors', function (Blueprint $table) {
            // Add new fields
            $table->string('shop_name')->nullable()->after('occupation');
            $table->string('visiting_card_photo')->nullable()->after('shop_name');
            $table->string('cnic_image')->nullable()->after('visiting_card_photo');
        });
    }

    public function down(): void
    {
        Schema::table('guarantors', function (Blueprint $table) {
            $table->dropColumn(['shop_name', 'visiting_card_photo', 'cnic_image']);
        });
    }
};
