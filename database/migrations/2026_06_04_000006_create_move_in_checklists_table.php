<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('move_in_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('inspection_member');
            $table->date('checklist_date');
            $table->enum('type', ['move_in', 'move_out'])->default('move_in');

            // 1. General Cleanliness
            $table->boolean('rooms_cleaned')->default(false);
            $table->boolean('kitchen_cleaned')->default(false);
            $table->boolean('bathrooms_cleaned')->default(false);
            $table->boolean('no_garbage')->default(false);

            // 2. Walls, Paint & Fixtures
            $table->boolean('no_wall_damage')->default(false);
            $table->boolean('paint_condition_ok')->default(false);
            $table->boolean('light_fixtures_ok')->default(false);
            $table->boolean('electric_wiring_ok')->default(false);
            $table->boolean('no_breaker_issues')->default(false);

            // 3. Furniture & Appliances
            $table->boolean('furniture_ok')->default(false);
            $table->boolean('ac_working')->default(false);
            $table->boolean('kitchen_appliances_ok')->default(false);
            $table->boolean('stove_clean')->default(false);
            $table->boolean('keys_returned')->default(false);

            // 4. Doors & Windows
            $table->boolean('doors_locks_ok')->default(false);
            $table->boolean('windows_ok')->default(false);
            $table->boolean('balcony_doors_ok')->default(false);

            // 5. Utilities & Dues
            $table->boolean('water_supply_ok')->default(false);
            $table->boolean('electricity_supply_ok')->default(false);
            $table->boolean('gas_supply_ok')->default(false);
            $table->boolean('no_pending_utility_bills')->default(false);
            $table->boolean('no_pending_maintenance')->default(false);
            $table->boolean('no_pending_rent')->default(false);

            // 6. Damage Report
            $table->text('damage_notes')->nullable();

            // 7. Inventory
            $table->boolean('fixtures_available')->default(false);
            $table->boolean('no_missing_items')->default(false);
            $table->text('inventory_notes')->nullable();

            // 8. Final
            $table->boolean('access_cards_returned')->default(false);
            $table->boolean('no_pending_requests')->default(false);
            $table->boolean('move_out_form_signed')->default(false);
            $table->enum('flat_condition', ['good', 'needs_repair'])->nullable();
            $table->decimal('deposit_deduction', 10, 2)->default(0);
            $table->text('final_remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('move_in_checklists');
    }
};
