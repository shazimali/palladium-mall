<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE agreements MODIFY COLUMN status ENUM('draft', 'active', 'expired', 'terminated') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE agreements MODIFY COLUMN status ENUM('active', 'expired', 'terminated') NOT NULL DEFAULT 'active'");
    }
};
