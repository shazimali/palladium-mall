<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class TenantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'tenants.view', 'display_name' => 'View Tenants', 'group' => 'Tenants'],
            ['name' => 'tenants.create', 'display_name' => 'Create Tenants', 'group' => 'Tenants'],
            ['name' => 'tenants.edit', 'display_name' => 'Edit Tenants', 'group' => 'Tenants'],
            ['name' => 'tenants.delete', 'display_name' => 'Delete Tenants', 'group' => 'Tenants'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Tenants')->pluck('id')
            );
        }
    }
}