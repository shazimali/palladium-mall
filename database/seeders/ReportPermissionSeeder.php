<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $financeGroup = \App\Models\PermissionGroup::where('name', 'finance')->first();

        $permissions = [
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'group' => 'Reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'group' => 'Reports'],
            ['name' => 'reports.profit_loss', 'display_name' => 'View Profit & Loss Report', 'group' => 'Reports'],
        ];

        foreach ($permissions as $p) {
            if ($financeGroup) {
                $p['permission_group_id'] = $financeGroup->id;
            }
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        // Assign to both 'admin', 'administrator', and 'accountant' (whichever exists)
        foreach (['super-admin', 'admin', 'administrator', 'accountant'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    Permission::where('group', 'Reports')->pluck('id')
                );
            }
        }
    }
}
