<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin User
        $superAdminUser = User::updateOrCreate(
            ['email' => 'super@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminUser->assignRole($superAdminRole);
        }

        // Create a regular Editor User
        $editorUser = User::updateOrCreate(
            ['email' => 'editor@palladium.com'],
            [
                'name' => 'Editor User',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorUser->assignRole($editorRole);
        }
    }
}
