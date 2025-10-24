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
            \Database\Seeders\CompleteNigerianLGASeeder::class,
            \Database\Seeders\NationalitiesSeeder::class,
            \Database\Seeders\BanksSeeder::class,
            \Database\Seeders\SettingsSeeder::class,
            \Database\Seeders\VerificationRulesSeeder::class,
            \Database\Seeders\RequiredLookupDataSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            CompanySeeder::class,
            DriverSeeder::class,
        ]);

    }

}
