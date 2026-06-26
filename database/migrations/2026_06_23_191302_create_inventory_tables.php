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
        // 1. Inventory Items (SKUs)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit_of_measure');
            $table->decimal('current_quantity', 12, 2)->default(0.00);
            $table->decimal('min_stock_level', 12, 2)->default(0.00);
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Stock Entries (Inflows)
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no')->unique();
            $table->date('date');
            $table->string('type')->default('IN'); // IN, ADJUST
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Stock Entry Line Items
        Schema::create('stock_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_entry_id')->constrained('stock_entries')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. Gate Passes (Outflows)
        Schema::create('gate_passes', function (Blueprint $table) {
            $table->id();
            $table->string('gatepass_no')->unique();
            $table->date('date');
            $table->string('issued_to');
            $table->string('purpose');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('status')->default('Issued'); // Issued, Cancelled
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 5. Gate Pass Line Items
        Schema::create('gate_pass_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_pass_id')->constrained('gate_passes')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_pass_items');
        Schema::dropIfExists('gate_passes');
        Schema::dropIfExists('stock_entry_items');
        Schema::dropIfExists('stock_entries');
        Schema::dropIfExists('inventory_items');
    }
};
