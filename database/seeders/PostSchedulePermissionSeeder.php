<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\PostScheduleHead;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PostSchedulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminGroup = PermissionGroup::where('name', 'admin')->first();

        $permissions = [
            // Post Schedule Heads
            ['name' => 'post_schedule_heads.view',   'display_name' => 'View Post Schedule Heads',   'group' => 'Post Schedule Heads'],
            ['name' => 'post_schedule_heads.create', 'display_name' => 'Create Post Schedule Heads', 'group' => 'Post Schedule Heads'],
            ['name' => 'post_schedule_heads.edit',   'display_name' => 'Edit Post Schedule Heads',   'group' => 'Post Schedule Heads'],
            ['name' => 'post_schedule_heads.delete', 'display_name' => 'Delete Post Schedule Heads', 'group' => 'Post Schedule Heads'],

            // Post Schedules
            ['name' => 'post_schedules.view',   'display_name' => 'View Post Schedules',   'group' => 'Post Schedules'],
            ['name' => 'post_schedules.create', 'display_name' => 'Create Post Schedules', 'group' => 'Post Schedules'],
            ['name' => 'post_schedules.edit',   'display_name' => 'Edit Post Schedules',   'group' => 'Post Schedules'],
            ['name' => 'post_schedules.delete', 'display_name' => 'Delete Post Schedules', 'group' => 'Post Schedules'],
            ['name' => 'post_schedules.print',  'display_name' => 'Print Post Schedules',  'group' => 'Post Schedules'],
        ];

        $superAdmin = Role::where('name', 'super_admin')->first();

        foreach ($permissions as $permData) {
            $perm = Permission::updateOrCreate(
                ['name' => $permData['name']],
                [
                    'display_name'        => $permData['display_name'],
                    'group'               => $permData['group'],
                    'permission_group_id' => $adminGroup?->id ?? 1,
                ]
            );

            if ($superAdmin && !$superAdmin->hasPermission($perm->name)) {
                $superAdmin->permissions()->syncWithoutDetaching([$perm->id]);
            }
        }

        // Seed default initial Post Schedule Heads if none exist
        if (PostScheduleHead::count() === 0) {
            $defaultHeads = [
                ['name' => 'Security & Guard Post',       'color' => 'blue',    'sort_order' => 1, 'description' => 'Mall security, entrance monitoring, parking patrol'],
                ['name' => 'Cleaning & Janitorial Post',   'color' => 'emerald', 'sort_order' => 2, 'description' => 'Corridor mop, restrooms cleaning, food court sanitation'],
                ['name' => 'Electrical & Maintenance',    'color' => 'amber',   'sort_order' => 3, 'description' => 'Generators, HVAC checks, lighting inspection'],
                ['name' => 'Office Admin & Front Desk',    'color' => 'purple',  'sort_order' => 4, 'description' => 'Reception, visitor logs, office coordination'],
                ['name' => 'Fire & Safety Post',          'color' => 'rose',    'sort_order' => 5, 'description' => 'Fire extinguisher checks, emergency exits clearance'],
            ];

            foreach ($defaultHeads as $head) {
                PostScheduleHead::create($head);
            }
        }
    }
}
