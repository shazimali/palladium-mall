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
            ['name' => 'utilities.view', 'display_name' => 'View Utilities', 'group' => 'Utilities'],
            ['name' => 'utilities.create', 'display_name' => 'Create Utilities', 'group' => 'Utilities'],
            ['name' => 'utilities.edit', 'display_name' => 'Edit Utilities', 'group' => 'Utilities'],
            ['name' => 'utilities.delete', 'display_name' => 'Delete Utilities', 'group' => 'Utilities'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Utilities')->pluck('id')
            );
        }
    }
}