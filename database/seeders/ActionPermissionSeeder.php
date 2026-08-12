<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ActionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Flats/Shops Actions
            ['name' => 'units.import', 'display_name' => 'Import Flats/Shops', 'group' => 'Flats/Shops'],
            ['name' => 'units.vacate', 'display_name' => 'Vacate Occupied Flats/Shops', 'group' => 'Flats/Shops'],
            ['name' => 'units.add-tenant', 'display_name' => 'Start Tenancy wizard from Flat/Shop', 'group' => 'Flats/Shops'],

            // Landlord property ownership
            ['name' => 'landlords.edit-units', 'display_name' => 'Edit Landlord Flats/Shops Ownership', 'group' => 'Landlords'],

            // Tenants wizard & checklists
            ['name' => 'tenants.wizard', 'display_name' => 'Run Tenant Registration Wizard', 'group' => 'Tenants'],
            ['name' => 'tenants.move-out', 'display_name' => 'Record Tenant Move-Out', 'group' => 'Tenants'],
            ['name' => 'tenants.print', 'display_name' => 'Print Tenant Agreements and Clearance Forms', 'group' => 'Tenants'],

            // Billing & utility readings
            ['name' => 'payments.record', 'display_name' => 'Record Billing Collections', 'group' => 'Billing'],
            ['name' => 'payments.bulk-generate', 'display_name' => 'Bulk Generate Billing', 'group' => 'Billing'],
            ['name' => 'payments.print', 'display_name' => 'Print Receipts & Invoices', 'group' => 'Billing'],
            ['name' => 'payments.whatsapp', 'display_name' => 'Share Bill on WhatsApp', 'group' => 'Billing'],
            ['name' => 'utilities.record', 'display_name' => 'Record Utility Meter Readings', 'group' => 'Billing'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id')
            );
        }
    }
}
