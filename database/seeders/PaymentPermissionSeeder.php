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
            ['name' => 'payments.view', 'display_name' => 'View Billing', 'group' => 'Billing'],
            ['name' => 'payments.create', 'display_name' => 'Create Billing', 'group' => 'Billing'],
            ['name' => 'payments.edit', 'display_name' => 'Edit Billing', 'group' => 'Billing'],
            ['name' => 'payments.delete', 'display_name' => 'Delete Billing', 'group' => 'Billing'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Billing')->pluck('id')
            );
        }
    }
}