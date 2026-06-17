<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Landlord;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Agreement;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('report index contains future projected payments for active agreements', function () {
    // 1. Create a super admin user
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $user->assignRole($role);

    // 2. Create landlord, unit, tenant, active agreement
    $landlord = Landlord::create([
        'name' => 'Test Landlord',
        'phone' => '0300-1234567',
        'email' => 'landlord@test.com',
        'cnic' => '35201-1111111-1',
    ]);

    $unit = Unit::create([
        'unit_number' => 'U-TEST-99',
        'type' => 'flat',
        'status' => 'rented',
        'landlord_id' => $landlord->id,
        'area_sqft' => 1000,
    ]);

    $tenant = Tenant::create([
        'name' => 'Test Tenant',
        'cnic' => '35201-2222222-2',
        'phone' => '0300-2222222',
        'email' => 'tenant@test.com',
        'status' => 'active',
    ]);

    // Active agreement: from 2 months ago to 10 months from now
    $agreement = Agreement::create([
        'tenant_id' => $tenant->id,
        'unit_id' => $unit->id,
        'start_date' => Carbon::now()->subMonths(2)->startOfMonth(),
        'end_date' => Carbon::now()->addMonths(10)->endOfMonth(),
        'monthly_rent' => 50000,
        'maintenance_charge' => 5000,
        'security_deposit' => 100000,
        'status' => 'active',
        'payment_due_day' => 5,
    ]);

    // No database payment recorded yet.

    // 3. Request report page for the current month
    $monthStr = Carbon::now()->startOfMonth()->toDateString();
    
    $response = $this->actingAs($user)
        ->get(route('reports.index', [
            'date_from' => $monthStr,
            'date_to' => $monthStr,
        ]));

    $response->assertStatus(200);

    // 4. Assert that projected entries are in the entries passed to the view
    $entries = $response->viewData('entries');
    
    expect($entries)->not->toBeEmpty();
    
    // We expect 2 projected entries (1 rent, 1 maintenance) for the current month
    $projectedRent = $entries->first(fn($e) => $e['type'] === 'rent' && $e['status'] === 'pending');
    $projectedMaint = $entries->first(fn($e) => $e['type'] === 'maintenance' && $e['status'] === 'pending');

    expect($projectedRent)->not->toBeNull();
    expect($projectedRent['amount_due'])->toBe(50000.0);
    expect($projectedRent['voucher_number'])->toBe('PM-PAY-PROJ');

    expect($projectedMaint)->not->toBeNull();
    expect($projectedMaint['amount_due'])->toBe(5000.0);
    expect($projectedMaint['voucher_number'])->toBe('PM-PAY-PROJ');
});

test('report index does not double-project if payment exists in database', function () {
    // 1. Create a super admin user
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'super-admin']);
    $user->assignRole($role);

    // 2. Create landlord, unit, tenant, active agreement
    $landlord = Landlord::create([
        'name' => 'Test Landlord',
        'phone' => '0300-1234567',
        'email' => 'landlord@test.com',
        'cnic' => '35201-1111111-1',
    ]);

    $unit = Unit::create([
        'unit_number' => 'U-TEST-99',
        'type' => 'flat',
        'status' => 'rented',
        'landlord_id' => $landlord->id,
        'area_sqft' => 1000,
    ]);

    $tenant = Tenant::create([
        'name' => 'Test Tenant',
        'cnic' => '35201-2222222-2',
        'phone' => '0300-2222222',
        'email' => 'tenant@test.com',
        'status' => 'active',
    ]);

    $agreement = Agreement::create([
        'tenant_id' => $tenant->id,
        'unit_id' => $unit->id,
        'start_date' => Carbon::now()->subMonths(2)->startOfMonth(),
        'end_date' => Carbon::now()->addMonths(10)->endOfMonth(),
        'monthly_rent' => 50000,
        'maintenance_charge' => 5000,
        'security_deposit' => 100000,
        'status' => 'active',
        'payment_due_day' => 5,
    ]);

    // Create a real payment for rent for the current month
    $month = Carbon::now()->startOfMonth();
    Payment::create([
        'tenant_id' => $tenant->id,
        'unit_id' => $unit->id,
        'agreement_id' => $agreement->id,
        'type' => 'rent',
        'month' => $month->toDateString(),
        'amount' => 50000,
        'amount_paid' => 50000,
        'status' => 'paid',
        'due_date' => $month->copy()->addDays(10)->toDateString(),
    ]);

    // 3. Request report page for the current month
    $response = $this->actingAs($user)
        ->get(route('reports.index', [
            'date_from' => $month->toDateString(),
            'date_to' => $month->toDateString(),
        ]));

    $response->assertStatus(200);

    $entries = $response->viewData('entries');

    // Should only have 1 projected entry (maintenance) and 1 real database entry (rent)
    $projectedRent = $entries->first(fn($e) => $e['type'] === 'rent' && $e['status'] === 'pending');
    $realRent = $entries->first(fn($e) => $e['type'] === 'rent' && $e['status'] === 'paid');
    $projectedMaint = $entries->first(fn($e) => $e['type'] === 'maintenance' && $e['status'] === 'pending');

    expect($projectedRent)->toBeNull();
    expect($realRent)->not->toBeNull();
    expect($projectedMaint)->not->toBeNull();
});
