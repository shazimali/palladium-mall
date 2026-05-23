<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AgreementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'agreements.view', 'display_name' => 'View Agreements', 'group' => 'Agreements'],
            ['name' => 'agreements.create', 'display_name' => 'Create Agreements', 'group' => 'Agreements'],
            ['name' => 'agreements.edit', 'display_name' => 'Edit Agreements', 'group' => 'Agreements'],
            ['name' => 'agreements.delete', 'display_name' => 'Delete Agreements', 'group' => 'Agreements'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Agreements')->pluck('id')
            );
        }
    }
}