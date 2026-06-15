<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ExpensePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Expense Heads
            ['name' => 'expense_heads.view', 'display_name' => 'View Expense Heads', 'group' => 'Expenses Management'],
            ['name' => 'expense_heads.create', 'display_name' => 'Create Expense Heads', 'group' => 'Expenses Management'],
            ['name' => 'expense_heads.edit', 'display_name' => 'Edit Expense Heads', 'group' => 'Expenses Management'],
            ['name' => 'expense_heads.delete', 'display_name' => 'Delete Expense Heads', 'group' => 'Expenses Management'],

            // Expenses
            ['name' => 'expenses.view', 'display_name' => 'View Expenses Ledger', 'group' => 'Expenses Management'],
            ['name' => 'expenses.create', 'display_name' => 'Record Expenses', 'group' => 'Expenses Management'],
            ['name' => 'expenses.edit', 'display_name' => 'Edit Recorded Expenses', 'group' => 'Expenses Management'],
            ['name' => 'expenses.delete', 'display_name' => 'Delete Recorded Expenses', 'group' => 'Expenses Management'],

            // Day Book
            ['name' => 'reports.daybook', 'display_name' => 'View Day Book Report', 'group' => 'Reports'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id')
            );
        }
    }
}
