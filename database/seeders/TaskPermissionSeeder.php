<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class TaskPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'tasks.view', 'display_name' => 'View Task Board & Details', 'group' => 'Task Management'],
            ['name' => 'tasks.create', 'display_name' => 'Create & Assign Tasks', 'group' => 'Task Management'],
            ['name' => 'tasks.edit', 'display_name' => 'Edit Task Status & Details', 'group' => 'Task Management'],
            ['name' => 'tasks.delete', 'display_name' => 'Delete Tasks', 'group' => 'Task Management'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $roles = Role::whereIn('name', ['super-admin', 'administrator'])->get();
        $permissionIds = Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id');
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
