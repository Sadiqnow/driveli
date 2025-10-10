<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RequiredLookupDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed nationalities table if it exists
        if (Schema::hasTable('nationalities')) {
            DB::table('nationalities')->insertOrIgnore([
                ['id' => 1, 'name' => 'Nigerian', 'code' => 'NG', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Ghanaian', 'code' => 'GH', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Kenyan', 'code' => 'KE', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'name' => 'South African', 'code' => 'ZA', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Seed states table if it exists
        if (Schema::hasTable('states')) {
            $states = [
                ['id' => 1, 'name' => 'Abia', 'code' => 'AB', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Adamawa', 'code' => 'AD', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Akwa Ibom', 'code' => 'AK', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'name' => 'Anambra', 'code' => 'AN', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 5, 'name' => 'Bauchi', 'code' => 'BA', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 6, 'name' => 'Bayelsa', 'code' => 'BY', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 7, 'name' => 'Benue', 'code' => 'BE', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 8, 'name' => 'Borno', 'code' => 'BO', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 9, 'name' => 'Cross River', 'code' => 'CR', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 10, 'name' => 'Delta', 'code' => 'DE', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 11, 'name' => 'Ebonyi', 'code' => 'EB', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 12, 'name' => 'Edo', 'code' => 'ED', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 13, 'name' => 'Ekiti', 'code' => 'EK', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 14, 'name' => 'Enugu', 'code' => 'EN', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 15, 'name' => 'FCT', 'code' => 'FC', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 16, 'name' => 'Gombe', 'code' => 'GO', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 17, 'name' => 'Imo', 'code' => 'IM', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 18, 'name' => 'Jigawa', 'code' => 'JI', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 19, 'name' => 'Kaduna', 'code' => 'KD', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 20, 'name' => 'Kano', 'code' => 'KN', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 21, 'name' => 'Katsina', 'code' => 'KT', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 22, 'name' => 'Kebbi', 'code' => 'KE', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 23, 'name' => 'Kogi', 'code' => 'KO', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 24, 'name' => 'Kwara', 'code' => 'KW', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 25, 'name' => 'Lagos', 'code' => 'LA', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 26, 'name' => 'Nasarawa', 'code' => 'NA', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 27, 'name' => 'Niger', 'code' => 'NI', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 28, 'name' => 'Ogun', 'code' => 'OG', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 29, 'name' => 'Ondo', 'code' => 'ON', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 30, 'name' => 'Osun', 'code' => 'OS', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 31, 'name' => 'Oyo', 'code' => 'OY', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 32, 'name' => 'Plateau', 'code' => 'PL', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 33, 'name' => 'Rivers', 'code' => 'RI', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 34, 'name' => 'Sokoto', 'code' => 'SO', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 35, 'name' => 'Taraba', 'code' => 'TA', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 36, 'name' => 'Yobe', 'code' => 'YO', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 37, 'name' => 'Zamfara', 'code' => 'ZA', 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($states as $state) {
                DB::table('states')->insertOrIgnore($state);
            }
        }

        // Seed local_governments table with sample data if it exists
        if (Schema::hasTable('local_governments')) {
            $lgas = [
                // Lagos LGAs (sample)
                ['id' => 1, 'name' => 'Agege', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2, 'name' => 'Ajeromi-Ifelodun', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 3, 'name' => 'Alimosho', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'name' => 'Amuwo-Odofin', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 5, 'name' => 'Apapa', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 6, 'name' => 'Badagry', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 7, 'name' => 'Epe', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 8, 'name' => 'Eti Osa', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 9, 'name' => 'Ibeju-Lekki', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 10, 'name' => 'Ifako-Ijaiye', 'state_id' => 25, 'created_at' => now(), 'updated_at' => now()],
                // Abuja LGAs (sample)
                ['id' => 11, 'name' => 'Abaji', 'state_id' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 12, 'name' => 'Bwari', 'state_id' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 13, 'name' => 'Gwagwalada', 'state_id' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 14, 'name' => 'Kuje', 'state_id' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 15, 'name' => 'Kwali', 'state_id' => 15, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 16, 'name' => 'Municipal Area Council', 'state_id' => 15, 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($lgas as $lga) {
                DB::table('local_governments')->insertOrIgnore($lga);
            }
        }
    }
}