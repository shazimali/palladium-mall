<?php

namespace Database\Seeders;

use App\Models\ExpenseHead;
use Illuminate\Database\Seeder;

class ExpenseHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $heads = [
            ['name' => 'Salaries & Wages', 'description' => 'Staff and employee salaries, wages, and bonuses.'],
            ['name' => 'Utility Bills (Common Area)', 'description' => 'Electricity, water, and gas charges for mall common spaces.'],
            ['name' => 'Repair & Maintenance', 'description' => 'HVAC maintenance, electrical repairs, plumbing work, etc.'],
            ['name' => 'Marketing & Advertising', 'description' => 'Social media campaigns, print media, banners, events.'],
            ['name' => 'Cleaning & Janitorial', 'description' => 'Cleaning supplies, contract janitor fees.'],
            ['name' => 'Security Services', 'description' => 'Security guards, CCTV maintenance.'],
            ['name' => 'Office Supplies & Stationery', 'description' => 'Printing, paper, files, office printer ink.'],
            ['name' => 'Entertainment & Tea', 'description' => 'Office tea, guest refreshments.'],
            ['name' => 'Others', 'description' => 'Miscellaneous expenses not categorized elsewhere.'],
        ];

        foreach ($heads as $head) {
            ExpenseHead::firstOrCreate(['name' => $head['name']], $head);
        }
    }
}
