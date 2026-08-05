<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SystemPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Reports Group
            ['name' => 'reports.cashbook', 'display_name' => 'View Cash Book Report', 'group' => 'Reports'],
            ['name' => 'reports.account_summary', 'display_name' => 'View Account Summary Report', 'group' => 'Reports'],
            ['name' => 'reports.daybook', 'display_name' => 'View Day Book Report', 'group' => 'Reports'],

            // General Receiving Vouchers Group
            ['name' => 'general_receiving_vouchers.view', 'display_name' => 'View General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
            ['name' => 'general_receiving_vouchers.create', 'display_name' => 'Create General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
            ['name' => 'general_receiving_vouchers.edit', 'display_name' => 'Edit General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
            ['name' => 'general_receiving_vouchers.delete', 'display_name' => 'Delete General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],
            ['name' => 'general_receiving_vouchers.search', 'display_name' => 'Search General Receiving Vouchers', 'group' => 'General Receiving Vouchers'],

            // Parties Group
            ['name' => 'parties.view', 'display_name' => 'View Parties', 'group' => 'Parties'],
            ['name' => 'parties.create', 'display_name' => 'Create Parties', 'group' => 'Parties'],
            ['name' => 'parties.edit', 'display_name' => 'Edit Parties', 'group' => 'Parties'],
            ['name' => 'parties.delete', 'display_name' => 'Delete Parties', 'group' => 'Parties'],

            // Receiving Vouchers Group
            ['name' => 'receiving_vouchers.search', 'display_name' => 'Search Receiving Vouchers', 'group' => 'Receiving Vouchers'],
            ['name' => 'receiving_vouchers.edit', 'display_name' => 'Edit Receiving Vouchers', 'group' => 'Receiving Vouchers'],
            ['name' => 'receiving_vouchers.print_list', 'display_name' => 'Print List Receiving Vouchers', 'group' => 'Receiving Vouchers'],

            // Expenses Management Group
            ['name' => 'expenses.search', 'display_name' => 'Search Expenses', 'group' => 'Expenses Management'],

            // Payment Vouchers Group
            ['name' => 'payment_vouchers.search', 'display_name' => 'Search Payment Vouchers', 'group' => 'Payment Vouchers'],
            ['name' => 'payment_vouchers.edit', 'display_name' => 'Edit Payment Vouchers', 'group' => 'Payment Vouchers'],

            // JV Vouchers Group
            ['name' => 'jv_vouchers.view', 'display_name' => 'View JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.create', 'display_name' => 'Create JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.edit', 'display_name' => 'Edit JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.delete', 'display_name' => 'Delete JV Vouchers', 'group' => 'JV Vouchers'],
            ['name' => 'jv_vouchers.print', 'display_name' => 'Print JV Vouchers', 'group' => 'JV Vouchers'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Attach missing permissions to all roles except regular read-only roles
        $roles = Role::all();
        $allPermissionIds = Permission::pluck('id');

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($allPermissionIds);
        }
    }
}
