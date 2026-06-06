<?php
 
namespace Database\Seeders;
 
use App\Models\Permission;
use App\Models\Role;
use App\Models\PaymentAccount;
use Illuminate\Database\Seeder;
 
class PaymentAccountPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed permissions
        $permissions = [
            ['name' => 'payment_accounts.view', 'display_name' => 'View Payment Accounts', 'group' => 'Payment Accounts'],
            ['name' => 'payment_accounts.create', 'display_name' => 'Create Payment Accounts', 'group' => 'Payment Accounts'],
            ['name' => 'payment_accounts.edit', 'display_name' => 'Edit Payment Accounts', 'group' => 'Payment Accounts'],
            ['name' => 'payment_accounts.delete', 'display_name' => 'Delete Payment Accounts', 'group' => 'Payment Accounts'],
        ];
 
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }
 
        // 2. Assign permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(
                Permission::where('group', 'Payment Accounts')->pluck('id')
            );
        }
 
        // 3. Seed default payment accounts
        $accounts = [
            [
                'name' => 'Cash Collection',
                'type' => 'cash',
                'notes' => 'Cash in hand collections at front office reception desk.',
                'is_active' => true,
            ],
            [
                'name' => 'JazzCash Business',
                'type' => 'bank_transfer',
                'bank_name' => 'Mobilink Microfinance Bank',
                'account_number' => '03001234567',
                'account_holder' => 'Palladium Mall Management',
                'notes' => 'Primary mobile wallet account.',
                'is_active' => true,
            ],
            [
                'name' => 'HBL Corporate Account',
                'type' => 'bank_transfer',
                'bank_name' => 'Habib Bank Limited',
                'account_number' => '1002003004005',
                'account_holder' => 'Palladium Mall Private Limited',
                'notes' => 'Main corporate bank account for bank transfers.',
                'is_active' => true,
            ],
        ];
 
        foreach ($accounts as $acc) {
            PaymentAccount::updateOrCreate(['name' => $acc['name']], $acc);
        }
    }
}
