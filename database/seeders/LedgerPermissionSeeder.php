<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LedgerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'ledger.view', 'display_name' => 'View Ledger', 'group' => 'Ledger'],
            ['name' => 'ledger.export', 'display_name' => 'Export Ledger', 'group' => 'Ledger'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Admin + Accountant both get ledger access
        foreach (['admin', 'accountant'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->permissions()->syncWithoutDetaching(
                    Permission::where('group', 'Ledger')->pluck('id')
                );
            }
        }
    }
}