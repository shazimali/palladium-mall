<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LandlordPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'landlords.view', 'display_name' => 'View Landlords', 'group' => 'Landlords'],
            ['name' => 'landlords.create', 'display_name' => 'Create Landlords', 'group' => 'Landlords'],
            ['name' => 'landlords.edit', 'display_name' => 'Edit Landlords', 'group' => 'Landlords'],
            ['name' => 'landlords.delete', 'display_name' => 'Delete Landlords', 'group' => 'Landlords'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign all landlord permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Landlords')->pluck('id')
            );
        }
    }
}
