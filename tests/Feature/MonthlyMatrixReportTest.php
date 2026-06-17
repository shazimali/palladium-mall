<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Landlord;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Agreement;
use App\Models\Payment;
use App\Models\PaymentAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('monthly matrix report shows correctly for units', function () {
    // 1. Create a super admin user
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $user->assignRole($role);

    // 2. Create landlords
    $landlord1 = Landlord::create([
        'name' => 'Landlord One',
        'phone' => '0300-1111111',
        'email' => 'l1@test.com',
        'cnic' => '35201-1111111-1',
    ]);

    // 3. Create units
    $unit1 = Unit::create([
        'unit_number' => 'Flat 101',
        'type' => 'flat',
        'status' => 'rented',
        'landlord_id' => $landlord1->id,
        'area_sqft' => 1200,
    ]);

    $unit2 = Unit::create([
        'unit_number' => 'Flat 102',
        'type' => 'flat',
        'status' => 'vacant',
        'landlord_id' => $landlord1->id,
        'area_sqft' => 1000,
    ]);

    // 4. Create tenants
    $tenant1 = Tenant::create([
        'name' => 'Tenant One',
        'cnic' => '35201-2222222-2',
        'phone' => '0300-2222222',
        'email' => 't1@test.com',
        'status' => 'active',
    ]);

    // 5. Create active agreement for Flat 101
    $agreement = Agreement::create([
        'tenant_id' => $tenant1->id,
        'unit_id' => $unit1->id,
        'start_date' => Carbon::now()->subMonths(1)->startOfMonth(),
        'end_date' => Carbon::now()->addMonths(11)->endOfMonth(),
        'monthly_rent' => 60000,
        'maintenance_charge' => 6000,
        'security_deposit' => 120000,
        'status' => 'active',
        'payment_due_day' => 10,
    ]);

    // 6. Create payment accounts
    $hbl = PaymentAccount::create(['name' => 'HBL']);
    $alfalah = PaymentAccount::create(['name' => 'Bank Alfalah']);

    // 7. Create payment for Flat 101
    $month = Carbon::now()->startOfMonth();
    $payment = Payment::create([
        'tenant_id' => $tenant1->id,
        'unit_id' => $unit1->id,
        'agreement_id' => $agreement->id,
        'type' => 'rent',
        'month' => $month->toDateString(),
        'amount' => 60000,
        'amount_paid' => 60000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'payment_account_id' => $hbl->id,
        'due_date' => $month->copy()->addDays(9)->toDateString(),
        'paid_at' => $month->copy()->addDays(9),
    ]);

    expect($payment->receipt_no)->toBe('PM-PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT));

    // 8. Generate monthly matrix report
    $response = $this->actingAs($user)
        ->get(route('reports.index', [
            'report_type' => 'monthly_matrix',
            'date_from' => $month->toDateString(),
        ]));

    $response->assertStatus(200);

    $entries = $response->viewData('entries');
    $summary = $response->viewData('summary');

    expect($entries)->toHaveCount(2); // Flat 101 and Flat 102
    
    $row101 = $entries->firstWhere('flat_no', 'Flat 101');
    $row102 = $entries->firstWhere('flat_no', 'Flat 102');

    expect($row101)->not->toBeNull();
    expect($row101['owner'])->toBe('Landlord One');
    expect($row101['status'])->toBe('RENTED');
    expect($row101['rent'])->toBe(60000.0);
    expect($row101['serv'])->toBe(6000.0); // Projected maintenance
    expect($row101['received'])->toBe(60000.0);
    expect($row101['pending'])->toBe(6000.0); // 66000 due - 60000 received
    expect($row101['payment_accounts']['HBL'])->toBe(60000.0);
    expect($row101['payment_accounts']['Bank Alfalah'])->toBe(0.0);

    expect($row102)->not->toBeNull();
    expect($row102['status'])->toBe('VACANT');
    expect($row102['rent'])->toBe(0.0);
    expect($row102['serv'])->toBe(0.0);
    expect($row102['received'])->toBe(0.0);
    expect($row102['pending'])->toBe(0.0);

    expect($summary['total_rent'])->toBe(60000.0);
    expect($summary['total_serv'])->toBe(6000.0);
    expect($summary['total_received'])->toBe(60000.0);
    expect($summary['accounts_total']['HBL'])->toBe(60000.0);
});
