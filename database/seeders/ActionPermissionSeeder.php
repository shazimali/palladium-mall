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
            // Units Actions
            ['name' => 'units.import', 'display_name' => 'Import Units', 'group' => 'Units'],
            ['name' => 'units.vacate', 'display_name' => 'Vacate Occupied Units', 'group' => 'Units'],
            ['name' => 'units.add-tenant', 'display_name' => 'Start Tenancy wizard from Unit', 'group' => 'Units'],
            ['name' => 'meters.edit', 'display_name' => 'Edit Utility Meters', 'group' => 'Units'],

            // Landlord property ownership
            ['name' => 'landlords.edit-units', 'display_name' => 'Edit Landlord Units Ownership', 'group' => 'Landlords'],

            // Tenants wizard & checklists
            ['name' => 'tenants.wizard', 'display_name' => 'Run Tenant Registration Wizard', 'group' => 'Tenants'],
            ['name' => 'tenants.move-out', 'display_name' => 'Record Tenant Move-Out', 'group' => 'Tenants'],
            ['name' => 'tenants.print', 'display_name' => 'Print Tenant Agreements and Clearance Forms', 'group' => 'Tenants'],

            // Payments & utility readings
            ['name' => 'payments.record', 'display_name' => 'Record Payments Collections', 'group' => 'Payments'],
            ['name' => 'payments.bulk-generate', 'display_name' => 'Bulk Generate Payments', 'group' => 'Payments'],
            ['name' => 'payments.print', 'display_name' => 'Print Receipts & Invoices', 'group' => 'Payments'],
            ['name' => 'payments.whatsapp', 'display_name' => 'Share Bill on WhatsApp', 'group' => 'Payments'],
            ['name' => 'utilities.record', 'display_name' => 'Record Utility Meter Readings', 'group' => 'Payments'],
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
