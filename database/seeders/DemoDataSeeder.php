<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Meter;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Landlord;
use App\Models\PaymentAccount;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo landlords...');
        $this->seedLandlords();

        $this->command->info('Seeding demo units...');
        $this->seedUnits();

        $this->command->info('Seeding demo unit ownerships...');
        $this->seedUnitOwnerships();

        $this->command->info('Seeding demo meters...');
        $this->seedMeters();

        $this->command->info('Seeding demo inspection persons...');
        $this->seedInspectionPersons();

        $this->command->info('Seeding demo tenants + agreements + checklists...');
        $this->seedTenants();

        $this->command->info('Seeding demo payment accounts...');
        $this->seedPaymentAccounts();

        $this->command->info('Seeding demo payments...');
        $this->seedPayments();

        $this->command->info('Seeding demo utility readings...');
        $this->seedUtilities();

        $this->command->info('Seeding demo expenses...');
        $this->seedExpenses();

        $this->command->info('Demo data seeded successfully.');
    }

    // -----------------------------------------------------------------------
    // Landlords
    // -----------------------------------------------------------------------
    private function seedLandlords(): void
    {
        Landlord::firstOrCreate(
            ['name' => 'Malik Riaz'],
            [
                'phone' => '0300-9876543',
                'email' => 'malik@riaz.com',
                'cnic' => '35201-1111111-1',
                'address' => 'Bahria Town, Lahore',
                'notes' => 'VVIP landlord portfolio owner'
            ]
        );

        Landlord::firstOrCreate(
            ['name' => 'Mian Mansha'],
            [
                'phone' => '0301-8765432',
                'email' => 'mansha@nishat.net',
                'cnic' => '35201-2222222-2',
                'address' => 'Nishat House, Gulberg, Lahore',
                'notes' => 'Premium commercial property landlord'
            ]
        );

        Landlord::firstOrCreate(
            ['name' => 'Sadruddin Hashwani'],
            [
                'phone' => '0302-7654321',
                'email' => 'hashwani@hashoo.com',
                'cnic' => '35201-3333333-3',
                'address' => 'Pearl Continental, Lahore',
                'notes' => 'Executive landlord investor'
            ]
        );
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

        $landlords = Landlord::pluck('id')->toArray();

        $units = [
            // Block A — Floor 1
            ['unit_number' => 'A-101', 'floor' => 'Floor 1', 'block' => 'Block A', 'type' => 'flat', 'status' => 'rented'],
            ['unit_number' => 'A-102', 'floor' => 'Floor 1', 'block' => 'Block A', 'type' => 'flat', 'status' => 'rented'],
            ['unit_number' => 'A-103', 'floor' => 'Floor 1', 'block' => 'Block A', 'type' => 'flat', 'status' => 'vacant'],

            // Block A — Floor 2
            ['unit_number' => 'A-201', 'floor' => 'Floor 2', 'block' => 'Block A', 'type' => 'flat', 'status' => 'rented'],
            ['unit_number' => 'A-202', 'floor' => 'Floor 2', 'block' => 'Block A', 'type' => 'flat', 'status' => 'rented'],

            // Block B — Floor 1
            ['unit_number' => 'B-101', 'floor' => 'Floor 1', 'block' => 'Block B', 'type' => 'flat', 'status' => 'rented'],
            ['unit_number' => 'B-102', 'floor' => 'Floor 1', 'block' => 'Block B', 'type' => 'flat', 'status' => 'vacant'],
            ['unit_number' => 'B-103', 'floor' => 'Floor 1', 'block' => 'Block B', 'type' => 'flat', 'status' => 'rented'],

            // Block B — Floor 2
            ['unit_number' => 'B-201', 'floor' => 'Floor 2', 'block' => 'Block B', 'type' => 'flat', 'status' => 'self'],

            // Ground Floor — Shops
            ['unit_number' => 'S-G01', 'floor' => 'Ground', 'block' => 'Block A', 'type' => 'shop', 'status' => 'rented'],
            ['unit_number' => 'S-G02', 'floor' => 'Ground', 'block' => 'Block A', 'type' => 'shop', 'status' => 'rented'],
            ['unit_number' => 'S-G03', 'floor' => 'Ground', 'block' => 'Block B', 'type' => 'shop', 'status' => 'vacant'],
        ];

        foreach ($units as $unitData) {
            $floorId = $floorMap[$unitData['floor']] ?? null;
            $blockId = $blockMap[$unitData['block']] ?? null;
            $areaId = $unitData['type'] === 'flat' ? $apartmentsZone->id : $retailZone->id;

            // Pick a deterministic landlord
            $landlordId = !empty($landlords) 
                ? $landlords[crc32($unitData['unit_number']) % count($landlords)] 
                : null;

            // Deterministic date (e.g. 12-24 months ago)
            $creationDate = Carbon::now()->subMonths(crc32($unitData['unit_number']) % 12 + 12)->toDateString();

            Unit::firstOrCreate(
                ['unit_number' => $unitData['unit_number']],
                [
                    'floor_id' => $floorId,
                    'block_id' => $blockId,
                    'area_id'  => $areaId,
                    'type'     => $unitData['type'],
                    'status'   => $unitData['status'],
                    'landlord_id' => $landlordId,
                    'date' => $creationDate,
                    'file_no'  => 'PALL-FILE-' . $unitData['unit_number'],
                    'area_sqft' => $unitData['type'] === 'flat' ? 1200.00 : 850.00,
                    'notes'    => 'Demo unit for Palladium Mall presentation.',
                ]
            );
        }
    }

    // -----------------------------------------------------------------------
    // Meters
    // -----------------------------------------------------------------------
    private function seedMeters(): void
    {
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

            // Skip if tenant already exists
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

            $agreement = Agreement::create([
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

            // Seed detailed extras for this tenant and agreement
            $this->seedTenantExtras($tenant, $agreement, $start);
        }
    }

    // -----------------------------------------------------------------------
    // Payment Accounts
    // -----------------------------------------------------------------------
    private function seedPaymentAccounts(): void
    {
        PaymentAccount::firstOrCreate(
            ['name' => 'HBL Collection A/C'],
            [
                'account_number' => '1234-56789-01',
                'account_holder' => 'Palladium Mall Collection',
                'bank_name' => 'Habib Bank Limited',
                'type' => 'bank_transfer',
                'is_active' => true,
                'notes' => 'Primary rent collection bank account'
            ]
        );

        PaymentAccount::firstOrCreate(
            ['name' => 'Al-Falah Rent Collection'],
            [
                'account_number' => '9876-54321-02',
                'account_holder' => 'Palladium Mall Administration',
                'bank_name' => 'Bank Alfalah',
                'type' => 'bank_transfer',
                'is_active' => true,
                'notes' => 'Alternate bank channel for incoming rent transfers'
            ]
        );

        PaymentAccount::firstOrCreate(
            ['name' => 'Petty Cash Box'],
            [
                'account_number' => 'CASH-001',
                'account_holder' => 'Cash Safe Vault',
                'bank_name' => 'Physical Vault',
                'type' => 'cash',
                'is_active' => true,
                'notes' => 'Direct cashier desk safe box'
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Payments
    // -----------------------------------------------------------------------
    private function seedPayments(): void
    {
        $tenants = Tenant::with(['unit', 'activeAgreement'])->get();
        $cashAcc = PaymentAccount::where('type', 'cash')->first();
        $bankAcc = PaymentAccount::where('type', 'bank_transfer')->first();

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

                    if ($isCurrentMonth) {
                        $scenario = match ($tenant->name) {
                            'Ahmed Raza' => ['status' => 'paid', 'paid' => $amount, 'method' => 'cash', 'acc' => $cashAcc],
                            'Sara Khan' => ['status' => 'paid', 'paid' => $amount, 'method' => 'bank_transfer', 'acc' => $bankAcc],
                            'Ali Malik' => ['status' => 'partial', 'paid' => $amount * 0.5, 'method' => 'cash', 'acc' => $cashAcc],
                            'Fatima Sheikh' => ['status' => 'unpaid', 'paid' => 0, 'method' => null, 'acc' => null],
                            'Usman Tariq' => ['status' => 'unpaid', 'paid' => 0, 'method' => null, 'acc' => null],
                            'Zainab Hussain' => ['status' => 'paid', 'paid' => $amount, 'method' => 'bank_transfer', 'acc' => $bankAcc],
                            'Zara Boutique' => ['status' => 'paid', 'paid' => $amount, 'method' => 'cheque', 'acc' => $bankAcc],
                            'Tech Zone' => ['status' => 'unpaid', 'paid' => 0, 'method' => null, 'acc' => null],
                            default => ['status' => 'unpaid', 'paid' => 0, 'method' => null, 'acc' => null],
                        };
                    } else {
                        $rand = rand(1, 10);
                        $method = ['cash', 'bank_transfer', 'cheque'][rand(0, 2)];
                        $acc = $method === 'cash' ? $cashAcc : $bankAcc;
                        $scenario = $rand <= 8
                            ? ['status' => 'paid', 'paid' => $amount, 'method' => $method, 'acc' => $acc]
                            : ['status' => 'unpaid', 'paid' => 0, 'method' => null, 'acc' => null];
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
                        'payment_account_id' => $scenario['acc'] ? $scenario['acc']->id : null,
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
    // Utility Readings (stored as Payments)
    // -----------------------------------------------------------------------
    private function seedUtilities(): void
    {
        $tenants = Tenant::with(['unit', 'activeAgreement'])->get();
        $cashAcc = PaymentAccount::where('type', 'cash')->first();
        $bankAcc = PaymentAccount::where('type', 'bank_transfer')->first();

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
                        Payment::where('unit_id', $tenant->unit_id)
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
                    $method = $isPaid ? (rand(0, 1) ? 'bank_transfer' : 'cash') : null;
                    $acc = $isPaid ? ($method === 'cash' ? $cashAcc : $bankAcc) : null;

                    $meterId = Meter::where('unit_id', $tenant->unit_id)
                        ->where('type', $type)
                        ->value('id');

                    Payment::create([
                        'unit_id' => $tenant->unit_id,
                        'tenant_id' => $tenant->id,
                        'agreement_id' => $agreement->id,
                        'type' => $type,
                        'month' => $month->toDateString(),
                        'previous_reading' => $prev,
                        'current_reading' => $curr,
                        'units_consumed' => $units,
                        'rate_per_unit' => $base['rate'],
                        'amount' => $bill,
                        'amount_paid' => $isPaid ? $bill : 0,
                        'due_date' => $dueDate->toDateString(),
                        'status' => $isPaid ? 'paid' : 'unpaid',
                        'paid_at' => $isPaid ? $month->copy()->addDays(rand(1, 14)) : null,
                        'meter_id' => $meterId,
                        'payment_method' => $method,
                        'payment_account_id' => $acc ? $acc->id : null,
                    ]);
                }
            }
        }
    }

    // -----------------------------------------------------------------------
    // Unit Ownerships
    // -----------------------------------------------------------------------
    private function seedUnitOwnerships(): void
    {
        $units = Unit::all();
        foreach ($units as $unit) {
            if ($unit->landlord_id) {
                \App\Models\UnitOwnership::firstOrCreate(
                    ['unit_id' => $unit->id, 'landlord_id' => $unit->landlord_id, 'is_current' => true],
                    [
                        'start_date' => $unit->date ?? now()->subYear()->toDateString(),
                        'nominee_name' => 'Muhammad ' . explode(' ', $unit->landlord->name)[0],
                        'nominee_relation_type' => 'son_of',
                        'nominee_relation_name' => $unit->landlord->name,
                        'total_amount' => 12000000.00,
                        'received_amount' => 10000000.00,
                        'credit_amount' => 2000000.00,
                        'received_from' => $unit->landlord->name,
                        'approved_by' => 'CEO Office',
                        'received_by' => 'Accounts Manager',
                        'approved_date' => $unit->date ?? now()->subYear()->toDateString(),
                        'notes' => 'Seeded ownership record for demo presentation.',
                    ]
                );
            }
        }
    }

    // -----------------------------------------------------------------------
    // Inspection Persons
    // -----------------------------------------------------------------------
    private function seedInspectionPersons(): void
    {
        \App\Models\InspectionPerson::firstOrCreate(
            ['name' => 'Waseem Akram'],
            [
                'designation' => 'Senior Facility Inspector',
                'phone' => '0321-1234567',
                'email' => 'waseem@palladium.com',
                'is_active' => true,
            ]
        );

        \App\Models\InspectionPerson::firstOrCreate(
            ['name' => 'Junaid Khan'],
            [
                'designation' => 'Assistant Building Inspector',
                'phone' => '0322-7654321',
                'email' => 'junaid@palladium.com',
                'is_active' => true,
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Tenant Extras (Guarantors, Contacts, Partners, Checklists)
    // -----------------------------------------------------------------------
    private function seedTenantExtras($tenant, $agreement, $start): void
    {
        // 1. Guarantor
        \App\Models\Guarantor::create([
            'tenant_id' => $tenant->id,
            'agreement_id' => $agreement->id,
            'name' => 'Mohammad Asif',
            'cnic' => '35201-9999999-9',
            'phone' => '0333-1234567',
            'relation' => 'relative',
            'address' => 'Model Town, Lahore',
            'occupation' => 'Retailer Business Owner',
            'shop_name' => 'Asif Traders',
        ]);

        // 2. Emergency Contact
        \App\Models\EmergencyContact::create([
            'tenant_id' => $tenant->id,
            'agreement_id' => $agreement->id,
            'name' => 'Zahid Mahmood',
            'relation' => 'brother',
            'phone' => '0345-1234567',
            'address' => 'Johar Town, Lahore',
        ]);

        // 3. Tenant Partner
        if (rand(0, 1) === 1) {
            \App\Models\TenantPartner::create([
                'tenant_id' => $tenant->id,
                'agreement_id' => $agreement->id,
                'name' => 'Arsalan Ahmad',
                'father_name' => 'Ahmad Mahmood',
                'cnic' => '35201-8888888-8',
                'gender' => 'male',
                'marital_status' => 'single',
                'phone' => '0312-3456789',
                'whatsapp_number' => '0312-3456789',
                'email' => 'arsalan@partner.com',
                'address' => 'Gulberg, Lahore',
                'occupation' => 'Co-Owner',
                'monthly_income' => 75000,
            ]);
        }

        // 4. Tenant Document Checklist
        \App\Models\TenantDocumentChecklist::create([
            'tenant_id' => $tenant->id,
            'agreement_id' => $agreement->id,
            'cnic_copy_tenant_front' => true,
            'cnic_copy_tenant_back' => true,
            'cnic_copy_father' => true,
            'cnic_copy_guarantor' => true,
            'passport_photo' => true,
            'nikah_nama' => false,
            'frc_form_b' => true,
            'police_verification' => true,
            'tenant_application_form' => true,
            'tenancy_agreement_copy' => true,
            'rules_acknowledgment' => true,
            'inspection_report' => true,
            'property_handover_form' => true,
            'security_deposit_receipt' => true,
            'meter_picture' => true,
            'emergency_contacts_added' => true,
            'guarantor_info_added' => true,
            'guarantor_business_card' => true,
            'tenant_business_card' => true,
            'property_advisor_card' => false,
            'old_tenant_verification' => false,
            'business_license' => $tenant->unit?->type === 'shop',
            'utility_bills_clearance' => true,
            'notes' => 'All basic verification documents checked and verified for the client presentation.',
        ]);

        // 5. Move In Checklist
        $inspector = \App\Models\InspectionPerson::first();
        \App\Models\MoveInChecklist::create([
            'tenant_id' => $tenant->id,
            'agreement_id' => $agreement->id,
            'inspection_person_id' => $inspector ? $inspector->id : null,
            'inspection_member' => $inspector ? $inspector->name : 'Facility Inspector',
            'checklist_date' => $start->toDateString(),
            'type' => 'move_in',
            'rooms_cleaned' => true,
            'kitchen_cleaned' => true,
            'bathrooms_cleaned' => true,
            'no_garbage' => true,
            'no_wall_damage' => true,
            'paint_condition_ok' => true,
            'light_fixtures_ok' => true,
            'electric_wiring_ok' => true,
            'no_breaker_issues' => true,
            'furniture_ok' => true,
            'ac_working' => true,
            'kitchen_appliances_ok' => true,
            'stove_clean' => true,
            'keys_returned' => true,
            'doors_locks_ok' => true,
            'windows_ok' => true,
            'balcony_doors_ok' => true,
            'water_supply_ok' => true,
            'electricity_supply_ok' => true,
            'gas_supply_ok' => true,
            'no_pending_utility_bills' => true,
            'no_pending_maintenance' => true,
            'no_pending_rent' => true,
            'fixtures_available' => true,
            'no_missing_items' => true,
            'flat_condition' => 'good',
            'final_remarks' => 'Unit handed over in pristine condition. All fittings verified.',
        ]);
    }

    // -----------------------------------------------------------------------
    // Expenses
    // -----------------------------------------------------------------------
    private function seedExpenses(): void
    {
        $heads = \App\Models\ExpenseHead::all();
        $accounts = PaymentAccount::all();
        $adminUser = \App\Models\User::first();

        if ($heads->isEmpty() || $accounts->isEmpty()) {
            return;
        }

        $cashAcc = $accounts->where('type', 'cash')->first() ?? $accounts->first();
        $bankAcc = $accounts->where('type', 'bank_transfer')->first() ?? $accounts->first();

        $expenseItems = [
            ['head' => 'Salaries & Wages', 'amount' => 120000, 'notes' => 'Staff salary for security and janitorial crew.', 'method' => 'bank_transfer', 'acc' => $bankAcc],
            ['head' => 'Utility Bills (Common Area)', 'amount' => 45000, 'notes' => 'LESCO common area commercial electric bill.', 'method' => 'bank_transfer', 'acc' => $bankAcc],
            ['head' => 'Repair & Maintenance', 'amount' => 15000, 'notes' => 'HVAC filter replacement & system servicing.', 'method' => 'cash', 'acc' => $cashAcc],
            ['head' => 'Entertainment & Tea', 'amount' => 3500, 'notes' => 'Office tea and refreshments for guest meetings.', 'method' => 'cash', 'acc' => $cashAcc],
            ['head' => 'Office Supplies & Stationery', 'amount' => 8500, 'notes' => 'Office printing paper and registration files.', 'method' => 'cash', 'acc' => $cashAcc],
            ['head' => 'Security Services', 'amount' => 25000, 'notes' => 'CCTV maintenance and monthly service fee.', 'method' => 'bank_transfer', 'acc' => $bankAcc],
        ];

        for ($monthsAgo = 2; $monthsAgo >= 0; $monthsAgo--) {
            $date = Carbon::now()->subMonths($monthsAgo)->startOfMonth()->addDays(5);

            foreach ($expenseItems as $item) {
                $headObj = $heads->where('name', $item['head'])->first();
                if (!$headObj) continue;

                // Adjust amount slightly per month for realism
                $amount = $item['amount'] + rand(-2000, 4000);

                \App\Models\Expense::create([
                    'expense_head_id' => $headObj->id,
                    'amount' => $amount,
                    'date' => $date->toDateString(),
                    'payment_method' => $item['method'],
                    'payment_account_id' => $item['acc']->id,
                    'reference' => 'EXP-' . $date->format('Ym') . '-' . rand(100, 999),
                    'notes' => $item['notes'],
                    'user_id' => $adminUser ? $adminUser->id : null,
                ]);
            }
        }
    }
}