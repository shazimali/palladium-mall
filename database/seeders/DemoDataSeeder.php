<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Meter;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\UtilityReading;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo units...');
        $this->seedUnits();

        $this->command->info('Seeding demo meters...');
        $this->seedMeters();

        $this->command->info('Seeding demo tenants + agreements...');
        $this->seedTenants();

        $this->command->info('Seeding demo payments...');
        $this->seedPayments();

        $this->command->info('Seeding demo utility readings...');
        $this->seedUtilities();

        $this->command->info('Seeding demo invoices...');
        $this->seedInvoices();

        $this->command->info('Demo data seeded successfully.');
    }

    // -----------------------------------------------------------------------
    // Units
    // -----------------------------------------------------------------------
    private function seedUnits(): void
    {
        // 1. Create/Retrieve Floors, Blocks, and Areas
        $floor1 = \App\Models\Floor::firstOrCreate(['name' => '1st']);
        $floor2 = \App\Models\Floor::firstOrCreate(['name' => '2nd']);
        $floor3 = \App\Models\Floor::firstOrCreate(['name' => '3rd']);
        $floor4 = \App\Models\Floor::firstOrCreate(['name' => '4th']);
        $ground = \App\Models\Floor::firstOrCreate(['name' => 'Ground']);

        $blockA = \App\Models\Block::firstOrCreate(['name' => 'Abubakar']);
        $blockB = \App\Models\Block::firstOrCreate(['name' => 'Usman']);

        $apartmentsZone = \App\Models\Area::firstOrCreate(['name' => 'Single']);
        $retailZone = \App\Models\Area::firstOrCreate(['name' => 'Double']);

        // Define mapping arrays
        $floorMap = [
            'Floor 1' => $floor1->id,
            'Floor 2' => $floor2->id,
            'Floor 3' => $floor3->id,
            'Floor 4' => $floor4->id,
            'Ground' => $ground->id,
        ];

        $blockMap = [
            'Block A' => $blockA->id,
            'Block B' => $blockB->id,
        ];

        $units = [
            // Block A — Floor 1
            ['unit_number' => 'A-101', 'floor' => 'Floor 1', 'block' => 'Block A', 'type' => 'flat', 'status' => 'occupied'],
            ['unit_number' => 'A-102', 'floor' => 'Floor 1', 'block' => 'Block A', 'type' => 'flat', 'status' => 'occupied'],
            ['unit_number' => 'A-103', 'floor' => 'Floor 1', 'block' => 'Block A', 'type' => 'flat', 'status' => 'vacant'],

            // Block A — Floor 2
            ['unit_number' => 'A-201', 'floor' => 'Floor 2', 'block' => 'Block A', 'type' => 'flat', 'status' => 'occupied'],
            ['unit_number' => 'A-202', 'floor' => 'Floor 2', 'block' => 'Block A', 'type' => 'flat', 'status' => 'occupied'],

            // Block B — Floor 1
            ['unit_number' => 'B-101', 'floor' => 'Floor 1', 'block' => 'Block B', 'type' => 'flat', 'status' => 'occupied'],
            ['unit_number' => 'B-102', 'floor' => 'Floor 1', 'block' => 'Block B', 'type' => 'flat', 'status' => 'vacant'],
            ['unit_number' => 'B-103', 'floor' => 'Floor 1', 'block' => 'Block B', 'type' => 'flat', 'status' => 'occupied'],

            // Block B — Floor 2
            ['unit_number' => 'B-201', 'floor' => 'Floor 2', 'block' => 'Block B', 'type' => 'flat', 'status' => 'sold'],

            // Ground Floor — Shops
            ['unit_number' => 'S-G01', 'floor' => 'Ground', 'block' => 'Block A', 'type' => 'shop', 'status' => 'occupied'],
            ['unit_number' => 'S-G02', 'floor' => 'Ground', 'block' => 'Block A', 'type' => 'shop', 'status' => 'occupied'],
            ['unit_number' => 'S-G03', 'floor' => 'Ground', 'block' => 'Block B', 'type' => 'shop', 'status' => 'vacant'],
        ];

        foreach ($units as $unitData) {
            $floorId = $floorMap[$unitData['floor']] ?? null;
            $blockId = $blockMap[$unitData['block']] ?? null;
            $areaId = $unitData['type'] === 'flat' ? $apartmentsZone->id : $retailZone->id;

            Unit::firstOrCreate(
                ['unit_number' => $unitData['unit_number']],
                [
                    'floor_id' => $floorId,
                    'block_id' => $blockId,
                    'area_id'  => $areaId,
                    'type'     => $unitData['type'],
                    'status'   => $unitData['status'],
                ]
            );
        }
    }

    // -----------------------------------------------------------------------
    // Meters
    // -----------------------------------------------------------------------
    private function seedMeters(): void
    {
        // Seed all 3 meter types for every unit that has a unit_number
        $meterMap = [
            'A-101' => ['electricity' => 'LESCO-A101-E', 'water' => 'WASA-A101-W', 'gas' => 'SNGPL-A101-G'],
            'A-102' => ['electricity' => 'LESCO-A102-E', 'water' => 'WASA-A102-W', 'gas' => 'SNGPL-A102-G'],
            'A-103' => ['electricity' => 'LESCO-A103-E', 'water' => 'WASA-A103-W', 'gas' => 'SNGPL-A103-G'],
            'A-201' => ['electricity' => 'LESCO-A201-E', 'water' => 'WASA-A201-W', 'gas' => 'SNGPL-A201-G'],
            'A-202' => ['electricity' => 'LESCO-A202-E', 'water' => 'WASA-A202-W', 'gas' => 'SNGPL-A202-G'],
            'B-101' => ['electricity' => 'LESCO-B101-E', 'water' => 'WASA-B101-W', 'gas' => 'SNGPL-B101-G'],
            'B-102' => ['electricity' => 'LESCO-B102-E', 'water' => 'WASA-B102-W', 'gas' => 'SNGPL-B102-G'],
            'B-103' => ['electricity' => 'LESCO-B103-E', 'water' => 'WASA-B103-W', 'gas' => 'SNGPL-B103-G'],
            'B-201' => ['electricity' => 'LESCO-B201-E', 'water' => 'WASA-B201-W', 'gas' => 'SNGPL-B201-G'],
            'S-G01' => ['electricity' => 'LESCO-SG01-E', 'water' => 'WASA-SG01-W', 'gas' => 'SNGPL-SG01-G'],
            'S-G02' => ['electricity' => 'LESCO-SG02-E', 'water' => 'WASA-SG02-W', 'gas' => 'SNGPL-SG02-G'],
            'S-G03' => ['electricity' => 'LESCO-SG03-E', 'water' => 'WASA-SG03-W', 'gas' => 'SNGPL-SG03-G'],
        ];

        foreach ($meterMap as $unitNumber => $meters) {
            $unit = Unit::where('unit_number', $unitNumber)->first();
            if (! $unit) continue;

            foreach ($meters as $type => $refNo) {
                Meter::firstOrCreate(
                    ['unit_id' => $unit->id, 'type' => $type],
                    ['meter_ref_no' => $refNo, 'is_active' => true]
                );
            }
        }
    }

    // -----------------------------------------------------------------------
    // Tenants + Agreements
    // -----------------------------------------------------------------------
    private function seedTenants(): void
    {
        $tenants = [
            [
                'unit' => 'A-101',
                'name' => 'Ahmed Raza',
                'cnic' => '35201-1234567-1',
                'phone' => '0300-1234567',
                'email' => 'ahmed.raza@email.com',
                'occupation' => 'Businessman',
                'rent' => 45000,
                'maintenance' => 2000,
                'start' => now()->subMonths(8),
                'months' => 12,
            ],
            [
                'unit' => 'A-102',
                'name' => 'Sara Khan',
                'cnic' => '35202-2345678-2',
                'phone' => '0301-2345678',
                'email' => 'sara.khan@email.com',
                'occupation' => 'Teacher',
                'rent' => 48000,
                'maintenance' => 2000,
                'start' => now()->subMonths(5),
                'months' => 12,
            ],
            [
                'unit' => 'A-201',
                'name' => 'Ali Malik',
                'cnic' => '35203-3456789-3',
                'phone' => '0302-3456789',
                'email' => 'ali.malik@email.com',
                'occupation' => 'Engineer',
                'rent' => 52000,
                'maintenance' => 2500,
                'start' => now()->subMonths(10),
                'months' => 12,
            ],
            [
                'unit' => 'A-202',
                'name' => 'Fatima Sheikh',
                'cnic' => '35204-4567890-4',
                'phone' => '0303-4567890',
                'email' => 'fatima.sheikh@email.com',
                'occupation' => 'Doctor',
                'rent' => 55000,
                'maintenance' => 2500,
                'start' => now()->subMonths(3),
                'months' => 12,
            ],
            [
                'unit' => 'B-101',
                'name' => 'Usman Tariq',
                'cnic' => '35205-5678901-5',
                'phone' => '0304-5678901',
                'email' => 'usman.tariq@email.com',
                'occupation' => 'Accountant',
                'rent' => 42000,
                'maintenance' => 2000,
                'start' => now()->subMonths(6),
                'months' => 12,
            ],
            [
                'unit' => 'B-103',
                'name' => 'Zainab Hussain',
                'cnic' => '35206-6789012-6',
                'phone' => '0305-6789012',
                'email' => 'zainab.h@email.com',
                'occupation' => 'Pharmacist',
                'rent' => 44000,
                'maintenance' => 2000,
                'start' => now()->subMonths(2),
                'months' => 12,
            ],
            [
                'unit' => 'S-G01',
                'name' => 'Zara Boutique',
                'cnic' => '35207-7890123-7',
                'phone' => '0306-7890123',
                'email' => 'zara.boutique@email.com',
                'occupation' => 'Retailer',
                'rent' => 95000,
                'maintenance' => 5000,
                'start' => now()->subMonths(14),
                'months' => 24,
            ],
            [
                'unit' => 'S-G02',
                'name' => 'Tech Zone',
                'cnic' => '35208-8901234-8',
                'phone' => '0307-8901234',
                'email' => 'techzone@email.com',
                'occupation' => 'Electronics',
                'rent' => 85000,
                'maintenance' => 4000,
                'start' => now()->subMonths(1),
                'months' => 12,
            ],
        ];

        foreach ($tenants as $td) {
            $unit = Unit::where('unit_number', $td['unit'])->first();
            if (!$unit)
                continue;

            // Skip if tenant already exists for this unit
            if (Tenant::where('unit_id', $unit->id)->exists())
                continue;

            $tenant = Tenant::create([
                'unit_id' => $unit->id,
                'name' => $td['name'],
                'cnic' => $td['cnic'],
                'phone' => $td['phone'],
                'email' => $td['email'],
                'occupation' => $td['occupation'],
                'status' => 'active',
            ]);

            $start = $td['start']->startOfMonth();
            $end = $start->copy()->addMonths($td['months'])->subDay();

            Agreement::create([
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => $start,
                'end_date' => $end,
                'monthly_rent' => $td['rent'],
                'maintenance_charge' => $td['maintenance'],
                'security_deposit' => $td['rent'] * 2,
                'grace_period_days' => 10,
                'fine_per_day' => 500,
                'status' => 'active',
                'terms' => 'No subletting. Tenant responsible for utility bills. Two months notice required.',
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // Payments — last 6 months
    // -----------------------------------------------------------------------
    private function seedPayments(): void
    {
        $tenants = Tenant::with(['unit', 'activeAgreement'])->get();

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $dueDate = $month->copy()->addDays(10);
            $isCurrentMonth = $monthsAgo === 0;

            foreach ($tenants as $tenant) {
                $agreement = $tenant->activeAgreement;
                if (!$agreement)
                    continue;

                // Skip months before agreement started
                if ($month->lt($agreement->start_date->startOfMonth()))
                    continue;

                foreach (['rent', 'maintenance'] as $type) {
                    $amount = $type === 'rent'
                        ? $agreement->monthly_rent
                        : $agreement->maintenance_charge;

                    // Skip if already exists
                    if (
                        Payment::where('tenant_id', $tenant->id)
                            ->where('type', $type)
                            ->where('month', $month->toDateString())
                            ->exists()
                    )
                        continue;

                    // Current month — some unpaid, some partial, some overdue
                    if ($isCurrentMonth) {
                        $scenario = match ($tenant->name) {
                            'Ahmed Raza' => ['status' => 'paid', 'paid' => $amount, 'method' => 'cash'],
                            'Sara Khan' => ['status' => 'paid', 'paid' => $amount, 'method' => 'bank_transfer'],
                            'Ali Malik' => ['status' => 'partial', 'paid' => $amount * 0.5, 'method' => 'cash'],
                            'Fatima Sheikh' => ['status' => 'unpaid', 'paid' => 0, 'method' => null],
                            'Usman Tariq' => ['status' => 'unpaid', 'paid' => 0, 'method' => null],
                            'Zainab Hussain' => ['status' => 'paid', 'paid' => $amount, 'method' => 'bank_transfer'],
                            'Zara Boutique' => ['status' => 'paid', 'paid' => $amount, 'method' => 'cheque'],
                            'Tech Zone' => ['status' => 'unpaid', 'paid' => 0, 'method' => null],
                            default => ['status' => 'unpaid', 'paid' => 0, 'method' => null],
                        };
                    } else {
                        // Past months — mostly paid, occasional overdue
                        $rand = rand(1, 10);
                        $scenario = $rand <= 8
                            ? ['status' => 'paid', 'paid' => $amount, 'method' => ['cash', 'bank_transfer', 'cheque'][rand(0, 2)]]
                            : ['status' => 'unpaid', 'paid' => 0, 'method' => null];
                    }

                    Payment::create([
                        'tenant_id' => $tenant->id,
                        'unit_id' => $tenant->unit_id,
                        'agreement_id' => $agreement->id,
                        'type' => $type,
                        'month' => $month->toDateString(),
                        'amount' => $amount,
                        'amount_paid' => $scenario['paid'],
                        'payment_method' => $scenario['method'],
                        'status' => $scenario['status'],
                        'due_date' => $dueDate->toDateString(),
                        'paid_at' => $scenario['status'] === 'paid'
                            ? $month->copy()->addDays(rand(1, 9))
                            : null,
                    ]);
                }
            }
        }
    }

    // -----------------------------------------------------------------------
    // Utility Readings — last 3 months
    // -----------------------------------------------------------------------
    private function seedUtilities(): void
    {
        $tenants = Tenant::with(['unit', 'activeAgreement'])->get();

        $baseReadings = [
            'electricity' => ['prev' => 12000, 'units' => 380, 'rate' => 25],
            'water' => ['prev' => 2000, 'units' => 180, 'rate' => 15],
            'gas' => ['prev' => 5000, 'units' => 160, 'rate' => 20],
        ];

        for ($monthsAgo = 2; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $dueDate = $month->copy()->addDays(15);
            $isCurrentMonth = $monthsAgo === 0;

            foreach ($tenants as $tenant) {
                $agreement = $tenant->activeAgreement;
                if (!$agreement)
                    continue;

                foreach (['electricity', 'water', 'gas'] as $type) {
                    if (
                        UtilityReading::where('unit_id', $tenant->unit_id)
                            ->where('type', $type)
                            ->where('month', $month->toDateString())
                            ->exists()
                    )
                        continue;

                    $base = $baseReadings[$type];
                    $variance = rand(-30, 50);
                    $units = $base['units'] + $variance;
                    $prev = $base['prev'] + ($monthsAgo * $base['units']);
                    $curr = $prev + $units;
                    $bill = round($units * $base['rate']);

                    $isPaid = $isCurrentMonth ? rand(0, 1) === 1 : rand(0, 3) !== 0;

                    UtilityReading::create([
                        'unit_id' => $tenant->unit_id,
                        'tenant_id' => $tenant->id,
                        'type' => $type,
                        'month' => $month->toDateString(),
                        'previous_reading' => $prev,
                        'current_reading' => $curr,
                        'units_consumed' => $units,
                        'rate_per_unit' => $base['rate'],
                        'bill_amount' => $bill,
                        'due_date' => $dueDate->toDateString(),
                        'status' => $isPaid ? 'paid' : 'unpaid',
                        'paid_at' => $isPaid ? $month->copy()->addDays(rand(1, 14)) : null,
                    ]);
                }
            }
        }
    }

    // -----------------------------------------------------------------------
    // Invoices — matching existing payments and utilities
    // -----------------------------------------------------------------------
    private function seedInvoices(): void
    {
        $invoiceService = app(InvoiceService::class);
        $tenants = Tenant::all();

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $dueDate = $month->copy()->addDays(15);

            foreach ($tenants as $tenant) {
                // Skip if invoice already exists for this tenant and month
                if (Invoice::where('tenant_id', $tenant->id)->where('month', $month->toDateString())->exists()) {
                    continue;
                }

                // Pull items for this month
                $items = $invoiceService->pullItems($tenant->id, $month->toDateString());

                if (empty($items)) {
                    continue;
                }

                // Create the invoice
                $invoice = $invoiceService->create(
                    tenant: $tenant,
                    month: $month->toDateString(),
                    dueDate: $dueDate->toDateString(),
                    items: $items,
                    notes: 'Demo invoice generated for ' . $month->format('F Y') . '.'
                );

                // Determine and update invoice status based on payment status
                // Rent payment status
                $rentPayment = Payment::where('tenant_id', $tenant->id)
                    ->where('month', $month->toDateString())
                    ->where('type', 'rent')
                    ->first();

                // Maintenance payment status
                $maintPayment = Payment::where('tenant_id', $tenant->id)
                    ->where('month', $month->toDateString())
                    ->where('type', 'maintenance')
                    ->first();

                // Check if both are paid (or if there are unpaid utilities)
                $allPaymentsPaid = true;
                if ($rentPayment && $rentPayment->status !== 'paid') {
                    $allPaymentsPaid = false;
                }
                if ($maintPayment && $maintPayment->status !== 'paid') {
                    $allPaymentsPaid = false;
                }

                // Check utility readings
                $unpaidUtilities = UtilityReading::where('tenant_id', $tenant->id)
                    ->where('month', $month->toDateString())
                    ->where('status', '!=', 'paid')
                    ->exists();

                if ($allPaymentsPaid && !$unpaidUtilities) {
                    $invoice->update(['status' => 'paid']);
                } else {
                    $invoice->update([
                        'status' => 'sent',
                        'sent_at' => $month->copy()->addDays(2),
                    ]);
                }
            }
        }
    }
}