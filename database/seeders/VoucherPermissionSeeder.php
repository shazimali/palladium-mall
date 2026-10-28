<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class VoucherPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'vouchers.view', 'display_name' => 'View Cash & Bank Vouchers', 'group' => 'Cash & Bank Vouchers'],
            ['name' => 'vouchers.create', 'display_name' => 'Create Cash & Bank Vouchers', 'group' => 'Cash & Bank Vouchers'],
            ['name' => 'vouchers.edit', 'display_name' => 'Edit Cash & Bank Vouchers', 'group' => 'Cash & Bank Vouchers'],
            ['name' => 'vouchers.delete', 'display_name' => 'Delete Cash & Bank Vouchers', 'group' => 'Cash & Bank Vouchers'],
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
