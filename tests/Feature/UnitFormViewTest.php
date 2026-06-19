<?php

use App\Models\User;
use App\Models\Unit;
use App\Models\Floor;
use App\Models\Block;
use App\Models\Area;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create unit form renders successfully', function () {
    // Create admin user and authenticate
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    // Create required structural lookups
    Floor::create(['name' => 'Ground']);
    Block::create(['name' => 'A']);
    Area::create(['name' => '100 sqft']);

    $response = $this->actingAs($user)->get(route('units.create'));

    $response->assertStatus(200);
});

test('edit unit form renders successfully when unit has no ownership', function () {
    // Create admin user and authenticate
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    // Create structural lookups and unit
    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);
    $area = Area::create(['name' => '100 sqft']);

    $unit = Unit::create([
        'unit_number' => 'G-101',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'area_id' => $area->id,
        'status' => 'vacant',
    ]);

    // Ensure the current ownership relationship returns null
    expect($unit->currentOwnership)->toBeNull();

    $response = $this->actingAs($user)->get(route('units.edit', $unit));

    $response->assertStatus(200);
});

test('storing a self-owned unit without self_maintenance_charge defaults it to 2500', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $response = $this->actingAs($user)->post(route('units.store'), [
        'unit_number' => 'G-102',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => '1',
        'self_maintenance_charge' => '',
    ]);

    $response->assertRedirect();
    $unit = Unit::where('unit_number', 'G-102')->first();
    expect($unit)->not->toBeNull();
    expect($unit->is_self)->toBeTrue();
    expect((float)$unit->self_maintenance_charge)->toBe(2500.00);
});

test('storing a self-owned unit with explicit self_maintenance_charge keeps the custom value', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $response = $this->actingAs($user)->post(route('units.store'), [
        'unit_number' => 'G-103',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => '1',
        'self_maintenance_charge' => '3500.00',
    ]);

    $response->assertRedirect();
    $unit = Unit::where('unit_number', 'G-103')->first();
    expect($unit)->not->toBeNull();
    expect($unit->is_self)->toBeTrue();
    expect((float)$unit->self_maintenance_charge)->toBe(3500.00);
});

test('updating a unit to self-owned without self_maintenance_charge defaults it to 2500', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $unit = Unit::create([
        'unit_number' => 'G-104',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => false,
        'self_maintenance_charge' => null,
    ]);

    $response = $this->actingAs($user)->put(route('units.update', $unit), [
        'unit_number' => 'G-104',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => '1',
        'self_maintenance_charge' => '',
    ]);

    $response->assertRedirect();
    $unit->refresh();
    expect($unit->is_self)->toBeTrue();
    expect((float)$unit->self_maintenance_charge)->toBe(2500.00);
});

test('updating a unit with is_self false sets self_maintenance_charge to null', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin']);
    $user->assignRole($role);

    $floor = Floor::create(['name' => 'Ground']);
    $block = Block::create(['name' => 'A']);

    $unit = Unit::create([
        'unit_number' => 'G-105',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => true,
        'self_maintenance_charge' => 2500.00,
    ]);

    $response = $this->actingAs($user)->put(route('units.update', $unit), [
        'unit_number' => 'G-105',
        'type' => 'flat',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'is_self' => '0',
        'self_maintenance_charge' => '2500.00',
    ]);

    $response->assertRedirect();
    $unit->refresh();
    expect($unit->is_self)->toBeFalse();
    expect($unit->self_maintenance_charge)->toBeNull();
});
