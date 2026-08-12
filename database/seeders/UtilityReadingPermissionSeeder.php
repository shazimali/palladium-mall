<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UtilityReadingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'utility_readings.view', 'display_name' => 'View Utility Meter Readings', 'group' => 'Utility Meter Readings'],
            ['name' => 'utility_readings.edit', 'display_name' => 'Edit Utility Meter Readings', 'group' => 'Utility Meter Readings'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        $roles = Role::whereIn('name', ['super-admin', 'administrator', 'admin'])->get();
        $permissionIds = Permission::whereIn('name', ['utility_readings.view', 'utility_readings.edit'])->pluck('id');

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}