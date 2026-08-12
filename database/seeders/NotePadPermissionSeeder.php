<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class NotePadPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'note_pads.view', 'display_name' => 'View Note Pad', 'group' => 'Note Pad'],
            ['name' => 'note_pads.create', 'display_name' => 'Create Note Pad', 'group' => 'Note Pad'],
            ['name' => 'note_pads.edit', 'display_name' => 'Edit Note Pad', 'group' => 'Note Pad'],
            ['name' => 'note_pads.delete', 'display_name' => 'Delete Note Pad', 'group' => 'Note Pad'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        $roles = Role::whereIn('name', ['super-admin', 'administrator', 'admin'])->get();
        $permissionIds = Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id');

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
