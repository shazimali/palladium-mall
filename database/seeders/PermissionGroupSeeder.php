<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $adminGroup = PermissionGroup::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Admin Operations',
                'description'  => 'Includes Operational, Administrative, Property, Tenant, and System Management modules.',
            ]
        );

        $financeGroup = PermissionGroup::updateOrCreate(
            ['name' => 'finance'],
            [
                'display_name' => 'Finance & Accounting',
                'description'  => 'Includes Billing, Vouchers, Ledgers, Accounts, Expenses, Inventory, and Financial Reports modules.',
            ]
        );

        $adminSubGroups = [
            'User Management',
            'Role Management',
            'Permission Management',
            'System Auditing',
            'Flats/Shops',
            'Tenants',
            'Agreements',
            'Landlords',
            'Other Tenants',
            'Owners',
            'Inspection Persons',
            'Report Types',
            'Inspection Heads',
            'Inspection Reports',
            'Flat Inspection',
            'Post Schedule Heads',
            'Post Schedules',
            'Task Management',
            'Note Pad',
            'Utility Meter Readings',
        ];

        $financeSubGroups = [
            'Billing',
            'Cash and Bank Accounts',
            'Expenses Management',
            'Receiving Vouchers',
            'Payment Vouchers',
            'General Receiving Vouchers',
            'JV Vouchers',
            'Other Owned Rent Purchase Vouchers',
            'Ledgers Management',
            'Inventory Management',
            'Parties',
            'Reports',
        ];

        Permission::whereIn('group', $adminSubGroups)->update(['permission_group_id' => $adminGroup->id]);
        Permission::whereIn('group', $financeSubGroups)->update(['permission_group_id' => $financeGroup->id]);

        // Default any remaining unassigned permissions to Admin group
        Permission::whereNull('permission_group_id')->update(['permission_group_id' => $adminGroup->id]);

        // Sync major groups to Super Admin and Admin roles
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissionGroups()->sync([$adminGroup->id, $financeGroup->id]);
        }

        $administrator = Role::whereIn('name', ['administrator', 'admin'])->get();
        foreach ($administrator as $role) {
            $role->permissionGroups()->syncWithoutDetaching([$adminGroup->id, $financeGroup->id]);
        }
    }
}
