<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LedgerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'ledgers.view', 'display_name' => 'View Ledgers', 'group' => 'Ledgers Management'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign to Admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $permissionIds = Permission::whereIn('group', ['Ledgers Management'])->pluck('id');
            $admin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
