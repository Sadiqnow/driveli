<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            \Database\Seeders\LookupTablesSeeder::class,
            \Database\Seeders\NigerianStatesAndLGASeeder::class,
            AdminUserSeeder::class,
            CompanySeeder::class,
            DriverSeeder::class,
        ]);

        
    }

}
