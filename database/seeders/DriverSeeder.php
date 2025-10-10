<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DriverNormalized as Driver;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DriverSeeder extends Seeder
{
    public function run()
    {
        // Since the DriverNormalized table structure is different,
        // we'll skip driver seeding for now to avoid schema conflicts.
        // Drivers will be created through the admin interface.
        
        $this->command->info('Driver seeding skipped - use admin interface to create drivers');
        
        // Uncomment and modify this section once the schema is stable
        /*
        // Create sample verified driver
        $driver1 = Driver::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'driver_id' => \App\Helpers\DrivelinkHelper::generateDriverId(),
                'first_name' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+2348012345678',
                'date_of_birth' => Carbon::parse('1985-06-15'),
                'gender' => 'male',
                'nationality_id' => 1,
                'nin_number' => '12345678901',
                'license_number' => 'LIC001234567',
                'license_class' => 'Commercial',
                'license_expiry_date' => Carbon::now()->addYears(3),
                'verification_status' => 'verified',
                'status' => 'active',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'registered_at' => now(),
            ]
        );
        */
    }
}