<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class InvoicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'invoices.view', 'display_name' => 'View Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.create', 'display_name' => 'Create Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.edit', 'display_name' => 'Edit Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.delete', 'display_name' => 'Delete Invoices', 'group' => 'Invoices'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Invoices')->pluck('id')
            );
        }
    }
}