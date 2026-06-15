<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentAutomationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a dedicated test unit
        $unit = Unit::firstOrCreate(
            ['unit_number' => 'Auto-Test-Unit-99'],
            [
                'type' => 'shop',
                'status' => 'rented',
                'area_sqft' => 500.00,
            ]
        );

        // 2. Create a test tenant
        $tenant = Tenant::firstOrCreate(
            ['cnic' => '35201-9999999-9'],
            [
                'unit_id' => $unit->id,
                'name' => 'Automation Test Tenant',
                'phone' => '0300-9999999',
                'email' => 'auto.test@email.com',
                'status' => 'active',
            ]
        );

        // Ensure the unit is linked to this tenant
        $unit->update(['status' => 'rented']);

        // 3. Create an active agreement with a 15th payment due day, 2 grace days, and Rs. 250/day fine
        $startDate = Carbon::today()->subMonths(3)->startOfMonth();
        $endDate = Carbon::today()->addMonths(9)->endOfMonth();

        $agreement = Agreement::where('tenant_id', $tenant->id)->first();
        if (!$agreement) {
            $agreement = Agreement::create([
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'monthly_rent' => 35000.00,
                'maintenance_charge' => 3500.00,
                'security_deposit' => 70000.00,
                'payment_due_day' => 15,
                'grace_period_days' => 2,
                'fine_per_day' => 250.00,
                'status' => 'active',
                'terms' => 'Automated job testing terms.',
            ]);
        }

        // 4. Create an overdue rent payment from last month to trigger the late fine job
        $lastMonth = Carbon::today()->subMonth()->startOfMonth();
        $dueDay = 15;
        $lastMonthDueDate = $lastMonth->copy()->day($dueDay);

        // Seed an unpaid rent payment for last month
        Payment::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'agreement_id' => $agreement->id,
                'type' => 'rent',
                'month' => $lastMonth->toDateString(),
            ],
            [
                'unit_id' => $unit->id,
                'amount' => 35000.00,
                'amount_paid' => 0.00,
                'status' => 'unpaid',
                'due_date' => $lastMonthDueDate->toDateString(),
            ]
        );
    }
}
