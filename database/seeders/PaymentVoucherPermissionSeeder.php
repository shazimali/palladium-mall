<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PaymentVoucherPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'payment_vouchers.view', 'display_name' => 'View Payment Vouchers', 'group' => 'Payment Vouchers'],
            ['name' => 'payment_vouchers.create', 'display_name' => 'Create Payment Vouchers', 'group' => 'Payment Vouchers'],
            ['name' => 'payment_vouchers.print', 'display_name' => 'Print Payment Vouchers', 'group' => 'Payment Vouchers'],
            ['name' => 'payment_vouchers.delete', 'display_name' => 'Delete Payment Vouchers', 'group' => 'Payment Vouchers'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Assign to Admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $permissionIds = Permission::whereIn('group', ['Payment Vouchers'])->pluck('id');
            $admin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
