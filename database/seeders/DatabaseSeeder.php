<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\State;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $seeders = [];

        // Only seed lookup tables if any are empty to prevent unnecessary operations
        if (DB::table('nationalities')->count() == 0 || DB::table('banks')->count() == 0 || State::count() == 0) {
            $seeders[] = \Database\Seeders\LookupTablesSeeder::class;
        }

        $seeders[] = \Database\Seeders\NigerianStatesAndLGASeeder::class;
        $seeders[] = AdminUserSeeder::class;
        $seeders[] = CompanySeeder::class;
        $seeders[] = DriverSeeder::class;

        $this->call($seeders);
    }

}
