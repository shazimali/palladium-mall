<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = Role::updateOrCreate(
            ['name' => 'super-admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Has all access. Bypasses permission checks.'
            ]
        );

        // 2. Administrator
        $admin = Role::updateOrCreate(
            ['name' => 'administrator'],
            [
                'display_name' => 'Administrator',
                'description' => 'Has full access except managing super admin settings.'
            ]
        );

        // Assign all permissions except super admin specific if any to Administrator
        $allPermissions = Permission::all();
        $admin->permissions()->sync($allPermissions->pluck('id')->toArray());

        // 3. Editor / Moderator
        $editor = Role::updateOrCreate(
            ['name' => 'editor'],
            [
                'display_name' => 'Editor',
                'description' => 'Can view and edit but not delete roles/permissions.'
            ]
        );

        $editorPermissions = Permission::whereIn('name', [
            'admin.access',
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
            'permissions.view'
        ])->get();

        $editor->permissions()->sync($editorPermissions->pluck('id')->toArray());
    }
}
