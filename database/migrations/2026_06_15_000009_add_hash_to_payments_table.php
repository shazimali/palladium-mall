<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('hash')->nullable()->unique()->after('id');
        });

        // Seed existing rows with UUIDs
        $payments = DB::table('payments')->whereNull('hash')->get();
        foreach ($payments as $payment) {
            DB::table('payments')->where('id', $payment->id)->update([
                'hash' => (string) Str::uuid()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('hash');
        });
    }
};
