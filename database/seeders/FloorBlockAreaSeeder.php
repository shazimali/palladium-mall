<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Floor;
use App\Models\Block;
use App\Models\Area;

class FloorBlockAreaSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Floors
        $floors = ['Ground', '1st', '2nd', '3rd', '4th'];
        foreach ($floors as $floorName) {
            Floor::firstOrCreate(['name' => $floorName]);
        }

        // Seed Blocks
        $blocks = ['Abubakar', 'Usman'];
        foreach ($blocks as $blockName) {
            Block::firstOrCreate(['name' => $blockName]);
        }

        // Seed Areas
        $areas = ['Single', 'Double'];
        foreach ($areas as $areaName) {
            Area::firstOrCreate(['name' => $areaName]);
        }
    }
}
