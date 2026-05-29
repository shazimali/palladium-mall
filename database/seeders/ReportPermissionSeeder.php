<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'group' => 'Reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'group' => 'Reports'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign to both 'admin', 'administrator', and 'accountant' (whichever exists)
        foreach (['admin', 'administrator', 'accountant'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    Permission::where('group', 'Reports')->pluck('id')
                );
            }
        }
    }
}
