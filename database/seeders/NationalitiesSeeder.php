<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NationalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Skip seeding if nationalities table already has data
        if (DB::table('nationalities')->count() > 0) {
            $this->command->info('Nationalities table already has data, skipping seeding.');
            return;
        }

        $nationalities = [
            ['name' => 'Nigerian', 'code' => 'NG', 'is_active' => true],
            ['name' => 'Ghanaian', 'code' => 'GH', 'is_active' => true],
            ['name' => 'Beninese', 'code' => 'BJ', 'is_active' => true],
            ['name' => 'Togolese', 'code' => 'TG', 'is_active' => true],
            ['name' => 'Cameroonian', 'code' => 'CM', 'is_active' => true],
            ['name' => 'American', 'code' => 'US', 'is_active' => true],
            ['name' => 'British', 'code' => 'GB', 'is_active' => true],
            ['name' => 'Other', 'code' => 'XX', 'is_active' => true],
        ];

        $timestamp = now();
        foreach ($nationalities as &$nationality) {
            $nationality['created_at'] = $timestamp;
            $nationality['updated_at'] = $timestamp;
        }

        DB::table('nationalities')->upsert($nationalities, ['code'], ['name', 'is_active', 'updated_at']);
        $this->command->info('Nationalities seeded successfully!');
    }
}
