<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class OtherTenantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'other_tenants.view',   'display_name' => 'View Other Tenants',          'group' => 'Other Tenants'],
            ['name' => 'other_tenants.create',  'display_name' => 'Create Other Tenants',        'group' => 'Other Tenants'],
            ['name' => 'other_tenants.edit',    'display_name' => 'Edit Other Tenants',          'group' => 'Other Tenants'],
            ['name' => 'other_tenants.delete',  'display_name' => 'Delete Other Tenants',        'group' => 'Other Tenants'],
            ['name' => 'other_tenants.attach',  'display_name' => 'Attach / Detach Other Tenants to Units', 'group' => 'Other Tenants'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Automatically grant all other-tenant permissions to the admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id')
            );
        }
    }
}
