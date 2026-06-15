<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class InspectionPersonPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'inspection_persons.view', 'display_name' => 'View Inspection Persons', 'group' => 'Inspection Persons'],
            ['name' => 'inspection_persons.create', 'display_name' => 'Create Inspection Persons', 'group' => 'Inspection Persons'],
            ['name' => 'inspection_persons.edit', 'display_name' => 'Edit Inspection Persons', 'group' => 'Inspection Persons'],
            ['name' => 'inspection_persons.delete', 'display_name' => 'Delete Inspection Persons', 'group' => 'Inspection Persons'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Inspection Persons')->pluck('id')
            );
        }
    }
}
