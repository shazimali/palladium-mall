<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class JvVoucherPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'jv_vouchers.view', 'display_name' => 'View JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.create', 'display_name' => 'Create JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.edit', 'display_name' => 'Edit JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.delete', 'display_name' => 'Delete JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.pay', 'display_name' => 'Mark JV Vouchers as Paid', 'group' => 'JV Vouchers'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id')
            );
        }
    }
}
