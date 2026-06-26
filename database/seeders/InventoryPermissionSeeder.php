<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class InventoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'inventory.view', 'display_name' => 'View Inventory', 'group' => 'Inventory Management'],
            ['name' => 'inventory.manage', 'display_name' => 'Manage Inventory Stock In', 'group' => 'Inventory Management'],
            ['name' => 'gatepasses.view', 'display_name' => 'View Gate Passes', 'group' => 'Inventory Management'],
            ['name' => 'gatepasses.manage', 'display_name' => 'Create/Manage Gate Passes', 'group' => 'Inventory Management'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign to Admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $permissionIds = Permission::whereIn('group', ['Inventory Management'])->pluck('id');
            $admin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
