<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class InspectionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminGroup = PermissionGroup::where('name', 'admin')->first();

        $permissions = [
            // Inspection Heads Setup
            ['name' => 'inspection_heads.view',   'display_name' => 'View Inspection Heads',   'group' => 'Inspection Heads'],
            ['name' => 'inspection_heads.create', 'display_name' => 'Create Inspection Heads', 'group' => 'Inspection Heads'],
            ['name' => 'inspection_heads.edit',   'display_name' => 'Edit Inspection Heads',   'group' => 'Inspection Heads'],
            ['name' => 'inspection_heads.delete', 'display_name' => 'Delete Inspection Heads', 'group' => 'Inspection Heads'],

            // Flat Inspection Reports
            ['name' => 'flat_inspections.view',   'display_name' => 'View Flat Inspections',   'group' => 'Flat Inspection'],
            ['name' => 'flat_inspections.create', 'display_name' => 'Create Flat Inspections', 'group' => 'Flat Inspection'],
            ['name' => 'flat_inspections.edit',   'display_name' => 'Edit Flat Inspections',   'group' => 'Flat Inspection'],
            ['name' => 'flat_inspections.delete', 'display_name' => 'Delete Flat Inspections', 'group' => 'Flat Inspection'],

            // Cleaning Inspection Reports
            ['name' => 'cleaning_inspections.view',   'display_name' => 'View Cleaning Reports',   'group' => 'Cleaning Inspection'],
            ['name' => 'cleaning_inspections.create', 'display_name' => 'Create Cleaning Reports', 'group' => 'Cleaning Inspection'],
            ['name' => 'cleaning_inspections.edit',   'display_name' => 'Edit Cleaning Reports',   'group' => 'Cleaning Inspection'],
            ['name' => 'cleaning_inspections.delete', 'display_name' => 'Delete Cleaning Reports', 'group' => 'Cleaning Inspection'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name'        => $perm['display_name'],
                    'group'               => $perm['group'],
                    'permission_group_id' => $adminGroup?->id,
                ]
            );
        }

        $this->command->info('Inspection permissions seeded: ' . count($permissions) . ' permissions.');
    }
}
