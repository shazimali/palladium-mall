<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class GeneralReceivingVoucherPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'general_receiving_vouchers.view', 'display_name' => 'View General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
            ['name' => 'general_receiving_vouchers.create', 'display_name' => 'Create General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
            ['name' => 'general_receiving_vouchers.delete', 'display_name' => 'Delete General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign all general receiving voucher permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'General Receiving Vouchers')->pluck('id')
            );
        }
    }
}
