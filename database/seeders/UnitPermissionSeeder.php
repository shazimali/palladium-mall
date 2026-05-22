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
            ['name' => 'units.view', 'display_name' => 'View Units', 'group' => 'Units'],
            ['name' => 'units.create', 'display_name' => 'Create Units', 'group' => 'Units'],
            ['name' => 'units.edit', 'display_name' => 'Edit Units', 'group' => 'Units'],
            ['name' => 'units.delete', 'display_name' => 'Delete Units', 'group' => 'Units'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign all unit permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Units')->pluck('id')
            );
        }
    }
}