<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UnitPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'units.view', 'display_name' => 'View Flats/Shops', 'group' => 'Flats/Shops'],
            ['name' => 'units.create', 'display_name' => 'Create Flats/Shops', 'group' => 'Flats/Shops'],
            ['name' => 'units.edit', 'display_name' => 'Edit Flats/Shops', 'group' => 'Flats/Shops'],
            ['name' => 'units.delete', 'display_name' => 'Delete Flats/Shops', 'group' => 'Flats/Shops'],
            ['name' => 'utility_meters_management', 'display_name' => 'Utility Meters Management Only (Flats/Shops)', 'group' => 'Flats/Shops'],
            ['name' => 'meters.edit', 'display_name' => 'Edit Utility Meters', 'group' => 'Flats/Shops'],
            ['name' => 'meters.delete', 'display_name' => 'Remove / Disconnect Utility Meters', 'group' => 'Flats/Shops'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        // Assign all unit permissions to super-admin & administrator roles
        $roles = Role::whereIn('name', ['super-admin', 'administrator'])->get();
        $permissionIds = Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id');
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}