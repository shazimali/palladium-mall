<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PartyPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'parties.view', 'display_name' => 'View Parties', 'group' => 'Parties'],
            ['name' => 'parties.create', 'display_name' => 'Create Parties', 'group' => 'Parties'],
            ['name' => 'parties.edit', 'display_name' => 'Edit Parties', 'group' => 'Parties'],
            ['name' => 'parties.delete', 'display_name' => 'Delete Parties', 'group' => 'Parties'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign all party permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Parties')->pluck('id')
            );
        }
    }
}
