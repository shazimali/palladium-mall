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
            InvoicePermissionSeeder::class,
            PaymentPermissionSeeder::class,
            LedgerPermissionSeeder::class,
            UtilityReadingPermissionSeeder::class,
            LandlordPermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
