<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter the enum type column to string to accept utility types (electricity, water, gas)
        DB::statement("ALTER TABLE payments MODIFY COLUMN type VARCHAR(50) NOT NULL");

        // 2. Add columns to payments table if they do not exist
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'meter_id')) {
                $table->foreignId('meter_id')->nullable()->constrained('meters')->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'previous_reading')) {
                $table->decimal('previous_reading', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('payments', 'current_reading')) {
                $table->decimal('current_reading', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('payments', 'units_consumed')) {
                $table->decimal('units_consumed', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('payments', 'rate_per_unit')) {
                $table->decimal('rate_per_unit', 10, 2)->nullable();
            }
        });

        // 3. Migrate existing utility_readings into payments
        if (Schema::hasTable('utility_readings')) {
            $readings = DB::table('utility_readings')->get();
            foreach ($readings as $reading) {
                // Ensure status is valid for payments ('paid', 'unpaid', 'partial')
                $status = $reading->status === 'paid' ? 'paid' : 'unpaid';

                // Check if this utility reading is already migrated to avoid unique constraint violations
                $exists = DB::table('payments')
                    ->where('tenant_id', $reading->tenant_id)
                    ->where('type', $reading->type)
                    ->where('month', $reading->month)
                    ->exists();

                if (!$exists) {
                    DB::table('payments')->insert([
                        'tenant_id'          => $reading->tenant_id,
                        'unit_id'            => $reading->unit_id,
                        'agreement_id'       => DB::table('agreements')
                                                    ->where('tenant_id', $reading->tenant_id)
                                                    ->where('status', 'active')
                                                    ->value('id') ?? DB::table('agreements')->where('tenant_id', $reading->tenant_id)->value('id') ?? 1,
                        'type'               => $reading->type, // 'electricity', 'water', 'gas'
                        'month'              => $reading->month,
                        'amount'             => $reading->bill_amount,
                        'amount_paid'        => $reading->status === 'paid' ? $reading->bill_amount : 0.00,
                        'status'             => $status,
                        'due_date'           => $reading->due_date,
                        'paid_at'            => $reading->paid_at,
                        'receipt'            => $reading->bill_proof,
                        'notes'              => $reading->notes,
                        'meter_id'           => $reading->meter_id,
                        'previous_reading'   => $reading->previous_reading,
                        'current_reading'    => $reading->current_reading,
                        'units_consumed'     => $reading->units_consumed,
                        'rate_per_unit'      => $reading->rate_per_unit,
                        'created_at'         => $reading->created_at,
                        'updated_at'         => $reading->updated_at,
                    ]);
                }
            }
        }

        // 4. Drop utility_readings
        Schema::dropIfExists('utility_readings');

        // 5. Drop invoices and invoice_items
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }

    public function down(): void
    {
        // Re-create dropped tables
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->date('month');
            $table->decimal('previous_reading', 12, 2);
            $table->decimal('current_reading', 12, 2);
            $table->decimal('units_consumed', 12, 2);
            $table->decimal('rate_per_unit', 10, 2);
            $table->decimal('bill_amount', 12, 2);
            $table->date('due_date');
            $table->string('status')->default('unpaid');
            $table->string('bill_proof')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('month');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'meter_id')) {
                $table->dropForeign(['meter_id']);
                $table->dropColumn('meter_id');
            }
            $table->dropColumn(['previous_reading', 'current_reading', 'units_consumed', 'rate_per_unit']);
        });

        // Revert column back to enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN type ENUM('rent', 'maintenance', 'fine', 'other') NOT NULL");
    }
};
