<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyRequest;
use App\Models\DriverNormalized;
use App\Models\CompanyMatch;

class CompanyMatchSeeder extends Seeder
{
    public function run()
    {
        $requests = CompanyRequest::where('status', 'active')->get();
        $drivers = DriverNormalized::where('status', 'verified')->limit(20)->get();

        if ($requests->isEmpty() || $drivers->isEmpty()) {
            $this->command->info('No active requests or verified drivers found. Skipping company matches seeding.');
            return;
        }

        foreach ($requests as $request) {
            $matchedDrivers = $drivers->random(min(3, $drivers->count()));

            foreach ($matchedDrivers as $driver) {
                CompanyMatch::firstOrCreate(
                    ['company_request_id' => $request->id, 'driver_id' => $driver->id],
                    [
                        'company_request_id' => $request->id,
                        'driver_id' => $driver->id,
                        'match_score' => rand(70, 95),
                        'matching_criteria' => json_encode([
                            'location_match' => rand(80, 100),
                            'experience_match' => rand(70, 95),
                            'vehicle_type_match' => rand(75, 100),
                            'availability_match' => rand(80, 100),
                        ]),
                        'status' => ['pending', 'accepted', 'rejected'][array_rand(['pending', 'accepted', 'rejected'])],
                        'rejection_reason' => null,
                        'accepted_at' => null,
                        'completed_at' => null,
                        'agreed_rate' => rand(150000, 300000),
                        'notes' => 'Auto-generated match',
                        'matched_by' => 1, // Assuming admin user ID 1
                    ]
                );
            }
        }

        $this->command->info('Company matches seeded successfully!');
    }
}
