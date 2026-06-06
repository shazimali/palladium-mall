<?php

use App\Models\Area;
use App\Models\Block;
use App\Models\Floor;
use App\Models\Landlord;
use App\Models\Role;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create unit with specific date', function () {
    // 1. Setup roles and permissions
    $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

    // Create a super admin
    $admin = User::factory()->create();
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $admin->assignRole($superAdminRole);

    // Create necessary relations
    $floor = Floor::create(['name' => 'Ground Floor', 'status' => 'active']);
    $block = Block::create(['name' => 'Block A', 'status' => 'active']);
    $area = Area::create(['name' => 'Main Area', 'status' => 'active']);
    $landlord = Landlord::create([
        'name' => 'John Landlord',
        'phone' => '1234567890',
        'email' => 'john@landlord.com',
        'cnic' => '12345-1234567-1'
    ]);

    $data = [
        'unit_number' => 'G-101',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'area_id' => $area->id,
        'landlord_id' => $landlord->id,
        'type' => 'flat',
        'status' => 'vacant',
        'area_sqft' => 1200,
        'date' => '2026-05-15',
    ];

    $response = $this->actingAs($admin)
        ->post(route('units.store'), $data);

    $response->assertRedirect(route('units.index'));

    $unit = Unit::where('unit_number', 'G-101')->first();
    expect($unit)->not->toBeNull();
    expect($unit->date->toDateString())->toBe('2026-05-15');
});

test('admin can create unit without date and it defaults to current date', function () {
    $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

    $admin = User::factory()->create();
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $admin->assignRole($superAdminRole);

    $floor = Floor::create(['name' => 'Ground Floor', 'status' => 'active']);
    $block = Block::create(['name' => 'Block A', 'status' => 'active']);
    $area = Area::create(['name' => 'Main Area', 'status' => 'active']);
    $landlord = Landlord::create([
        'name' => 'John Landlord',
        'phone' => '1234567890',
        'email' => 'john@landlord.com',
        'cnic' => '12345-1234567-1'
    ]);

    $data = [
        'unit_number' => 'G-102',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'area_id' => $area->id,
        'landlord_id' => $landlord->id,
        'type' => 'flat',
        'status' => 'vacant',
        'area_sqft' => 1200,
        'date' => '', // Empty date
    ];

    $response = $this->actingAs($admin)
        ->post(route('units.store'), $data);

    $response->assertRedirect(route('units.index'));

    $unit = Unit::where('unit_number', 'G-102')->first();
    expect($unit)->not->toBeNull();
    expect($unit->date->toDateString())->toBe(now()->toDateString());
});

test('admin can update unit date', function () {
    $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

    $admin = User::factory()->create();
    $superAdminRole = Role::where('name', 'super-admin')->first();
    $admin->assignRole($superAdminRole);

    $floor = Floor::create(['name' => 'Ground Floor', 'status' => 'active']);
    $block = Block::create(['name' => 'Block A', 'status' => 'active']);
    $area = Area::create(['name' => 'Main Area', 'status' => 'active']);
    $landlord = Landlord::create([
        'name' => 'John Landlord',
        'phone' => '1234567890',
        'email' => 'john@landlord.com',
        'cnic' => '12345-1234567-1'
    ]);

    $unit = Unit::create([
        'unit_number' => 'G-103',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'area_id' => $area->id,
        'landlord_id' => $landlord->id,
        'type' => 'flat',
        'status' => 'vacant',
        'area_sqft' => 1200,
        'date' => '2026-05-01',
    ]);

    $data = [
        'unit_number' => 'G-103',
        'floor_id' => $floor->id,
        'block_id' => $block->id,
        'area_id' => $area->id,
        'landlord_id' => $landlord->id,
        'type' => 'flat',
        'status' => 'vacant',
        'area_sqft' => 1200,
        'date' => '2026-06-01', // Updated date
    ];

    $response = $this->actingAs($admin)
        ->put(route('units.update', $unit->id), $data);

    $response->assertRedirect(route('units.index'));

    $unit->refresh();
    expect($unit->date->toDateString())->toBe('2026-06-01');
});
