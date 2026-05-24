<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PaymentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'payments.view', 'display_name' => 'View Payments', 'group' => 'Payments'],
            ['name' => 'payments.create', 'display_name' => 'Create Payments', 'group' => 'Payments'],
            ['name' => 'payments.edit', 'display_name' => 'Edit Payments', 'group' => 'Payments'],
            ['name' => 'payments.delete', 'display_name' => 'Delete Payments', 'group' => 'Payments'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Payments')->pluck('id')
            );
        }
    }
}