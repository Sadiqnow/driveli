<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LookupTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Call global lookup seeders here
        $this->call([
            NationalitiesSeeder::class,
            BanksSeeder::class,
            NigerianStatesAndLGASeeder::class,
        ]);
    }
}
