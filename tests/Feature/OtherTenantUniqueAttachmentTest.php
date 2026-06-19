<?php

use App\Models\User;
use App\Models\OtherTenant;
use App\Models\Unit;
use App\Models\Floor;
use App\Models\Block;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('storing a new tenant with already attached unit fails validation', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $unit = Unit::create([
        'unit_number' => 'G-101',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => true,
    ]);

    // Attach existing tenant to this unit
    OtherTenant::create([
        'name' => 'Tenant 1',
        'cnic' => '35201-1111111-1',
        'status' => 'active',
        'unit_id' => $unit->id,
    ]);

    $response = $this->actingAs($user)->post(route('other-tenants.store'), [
        'name' => 'New Tenant',
        'cnic' => '35201-2222222-2',
        'status' => 'active',
        'unit_id' => $unit->id,
    ]);

    $response->assertSessionHasErrors('unit_id');
});

test('updating an existing tenant to already attached unit fails validation', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $unit = Unit::create([
        'unit_number' => 'G-101',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => true,
    ]);

    // Tenant 1 is attached to G-101
    OtherTenant::create([
        'name' => 'Tenant 1',
        'cnic' => '35201-1111111-1',
        'status' => 'active',
        'unit_id' => $unit->id,
    ]);

    // Tenant 2 is unattached
    $tenant2 = OtherTenant::create([
        'name' => 'Tenant 2',
        'cnic' => '35201-2222222-2',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->put(route('other-tenants.update', $tenant2), [
        'name' => 'Tenant 2 Updated',
        'cnic' => '35201-2222222-2',
        'status' => 'active',
        'unit_id' => $unit->id,
    ]);

    $response->assertSessionHasErrors('unit_id');
});

test('updating a tenant can retain its own attached unit', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $unit = Unit::create([
        'unit_number' => 'G-101',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => true,
    ]);

    $tenant = OtherTenant::create([
        'name' => 'Tenant 1',
        'cnic' => '35201-1111111-1',
        'status' => 'active',
        'unit_id' => $unit->id,
    ]);

    $response = $this->actingAs($user)->put(route('other-tenants.update', $tenant), [
        'name' => 'Tenant 1 Updated Name',
        'cnic' => '35201-1111111-1',
        'status' => 'active',
        'unit_id' => $unit->id,
    ]);

    $response->assertSessionHasNoErrors();
});

test('cnic is required to save other tenant', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $response = $this->actingAs($user)->post(route('other-tenants.store'), [
        'name' => 'New Tenant',
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors('cnic');
});

test('cnic must match the pattern XXXXX-XXXXXXX-X', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    // Test with invalid format
    $response = $this->actingAs($user)->post(route('other-tenants.store'), [
        'name' => 'New Tenant',
        'cnic' => '35201-12345678-1', // Extra digit in middle
        'status' => 'active',
    ]);
    $response->assertSessionHasErrors('cnic');

    // Test with plain digits
    $response2 = $this->actingAs($user)->post(route('other-tenants.store'), [
        'name' => 'New Tenant 2',
        'cnic' => '3520112345671', // No dashes
        'status' => 'active',
    ]);
    $response2->assertSessionHasErrors('cnic');

    // Test with correct format
    $response3 = $this->actingAs($user)->post(route('other-tenants.store'), [
        'name' => 'New Tenant 3',
        'cnic' => '35201-1234567-1',
        'status' => 'active',
    ]);
    $response3->assertSessionHasNoErrors();
});

test('cnic must be unique across other tenants', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    OtherTenant::create([
        'name' => 'Tenant 1',
        'cnic' => '35201-1111111-1',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('other-tenants.store'), [
        'name' => 'Tenant 2',
        'cnic' => '35201-1111111-1', // Duplicate CNIC
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors('cnic');
});
