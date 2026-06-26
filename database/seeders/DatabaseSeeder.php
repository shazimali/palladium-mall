<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            ReportPermissionSeeder::class,
            UnitPermissionSeeder::class,
            TenantPermissionSeeder::class,
            AgreementPermissionSeeder::class,
            PaymentPermissionSeeder::class,
            UtilityReadingPermissionSeeder::class,
            LandlordPermissionSeeder::class,
            PaymentAccountPermissionSeeder::class,
            InspectionPersonPermissionSeeder::class,
            ActionPermissionSeeder::class,
            ExpenseHeadSeeder::class,
            ExpensePermissionSeeder::class,
            OtherTenantPermissionSeeder::class,
            OwnerAndVoucherPermissionSeeder::class,
            PaymentVoucherPermissionSeeder::class,
            LedgerPermissionSeeder::class,
            InventoryPermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            FloorBlockAreaSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
