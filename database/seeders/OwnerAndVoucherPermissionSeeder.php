<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class OwnerAndVoucherPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Owners
            ['name' => 'owners.view', 'display_name' => 'View Owners', 'group' => 'Owners'],
            ['name' => 'owners.create', 'display_name' => 'Create Owners', 'group' => 'Owners'],
            ['name' => 'owners.edit', 'display_name' => 'Edit Owners', 'group' => 'Owners'],
            ['name' => 'owners.delete', 'display_name' => 'Delete Owners', 'group' => 'Owners'],
            
            // Receiving Vouchers
            ['name' => 'receiving_vouchers.view', 'display_name' => 'View Receiving Vouchers', 'group' => 'Receiving Vouchers'],
            ['name' => 'receiving_vouchers.create', 'display_name' => 'Create Receiving Vouchers', 'group' => 'Receiving Vouchers'],
            ['name' => 'receiving_vouchers.print', 'display_name' => 'Print Receiving Vouchers', 'group' => 'Receiving Vouchers'],
            ['name' => 'receiving_vouchers.delete', 'display_name' => 'Delete Receiving Vouchers', 'group' => 'Receiving Vouchers'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign these permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $permissionIds = Permission::whereIn('group', ['Owners', 'Receiving Vouchers'])->pluck('id');
            $admin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
