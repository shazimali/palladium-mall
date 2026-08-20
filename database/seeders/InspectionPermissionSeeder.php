<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class InspectionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Report Types Master Setup
            ['name' => 'report_types.view',   'display_name' => 'View Report Types',   'group' => 'Report Types'],
            ['name' => 'report_types.create', 'display_name' => 'Create Report Types', 'group' => 'Report Types'],
            ['name' => 'report_types.edit',   'display_name' => 'Edit Report Types',   'group' => 'Report Types'],
            ['name' => 'report_types.delete', 'display_name' => 'Delete Report Types', 'group' => 'Report Types'],

            // Inspection Heads Setup
            ['name' => 'inspection_heads.view',   'display_name' => 'View Inspection Heads',   'group' => 'Inspection Heads'],
            ['name' => 'inspection_heads.create', 'display_name' => 'Create Inspection Heads', 'group' => 'Inspection Heads'],
            ['name' => 'inspection_heads.edit',   'display_name' => 'Edit Inspection Heads',   'group' => 'Inspection Heads'],
            ['name' => 'inspection_heads.delete', 'display_name' => 'Delete Inspection Heads', 'group' => 'Inspection Heads'],

            // Services Inspection Reports (Dynamic inspection reports per Report Type)
            ['name' => 'inspection_reports.view',   'display_name' => 'View Inspection Reports',   'group' => 'Inspection Reports'],
            ['name' => 'inspection_reports.create', 'display_name' => 'Create Inspection Reports', 'group' => 'Inspection Reports'],
            ['name' => 'inspection_reports.edit',   'display_name' => 'Edit Inspection Reports',   'group' => 'Inspection Reports'],
            ['name' => 'inspection_reports.delete', 'display_name' => 'Delete Inspection Reports', 'group' => 'Inspection Reports'],

            // Flat Inspection Reports
            ['name' => 'flat_inspections.view',   'display_name' => 'View Flat Inspections',   'group' => 'Flat Inspection'],
            ['name' => 'flat_inspections.create', 'display_name' => 'Create Flat Inspections', 'group' => 'Flat Inspection'],
            ['name' => 'flat_inspections.edit',   'display_name' => 'Edit Flat Inspections',   'group' => 'Flat Inspection'],
            ['name' => 'flat_inspections.delete', 'display_name' => 'Delete Flat Inspections', 'group' => 'Flat Inspection'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name' => $perm['display_name'],
                    'group'        => $perm['group'],
                ]
            );
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id')
            );
        }

        $this->command->info('Inspection permissions updated: ' . count($permissions) . ' permissions.');
    }
}
