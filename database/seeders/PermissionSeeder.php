<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users CRUD
            [
                'name' => 'users.view',
                'display_name' => 'View Users',
                'group' => 'User Management'
            ],
            [
                'name' => 'users.create',
                'display_name' => 'Create Users',
                'group' => 'User Management'
            ],
            [
                'name' => 'users.edit',
                'display_name' => 'Edit Users',
                'group' => 'User Management'
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Delete Users',
                'group' => 'User Management'
            ],
            // Roles CRUD
            [
                'name' => 'roles.view',
                'display_name' => 'View Roles',
                'group' => 'Role Management'
            ],
            [
                'name' => 'roles.create',
                'display_name' => 'Create Roles',
                'group' => 'Role Management'
            ],
            [
                'name' => 'roles.edit',
                'display_name' => 'Edit Roles',
                'group' => 'Role Management'
            ],
            [
                'name' => 'roles.delete',
                'display_name' => 'Delete Roles',
                'group' => 'Role Management'
            ],
            // Permissions CRUD
            [
                'name' => 'permissions.view',
                'display_name' => 'View Permissions',
                'group' => 'Permission Management'
            ],
            [
                'name' => 'permissions.create',
                'display_name' => 'Create Permissions',
                'group' => 'Permission Management'
            ],
            [
                'name' => 'permissions.edit',
                'display_name' => 'Edit Permissions',
                'group' => 'Permission Management'
            ],
            [
                'name' => 'permissions.delete',
                'display_name' => 'Delete Permissions',
                'group' => 'Permission Management'
            ],
            [
                'name' => 'activity_logs.view',
                'display_name' => 'View Activity Logs',
                'group' => 'System Auditing'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'group' => $permission['group']
                ]
            );
        }
    }
}
