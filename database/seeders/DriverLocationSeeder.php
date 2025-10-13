<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DriverLocationTracking;
use App\Models\DriverNormalized;
use Carbon\Carbon;

class DriverLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get active drivers
        $drivers = DriverNormalized::where('is_current', true)->take(10)->get();

        if ($drivers->isEmpty()) {
            return;
        }

        // Lagos coordinates (base location)
        $baseLat = 6.5244;
        $baseLng = 3.3792;

        foreach ($drivers as $driver) {
            // Generate 24 hours of location data (every 30 minutes)
            $startTime = Carbon::now()->subDay();

            for ($i = 0; $i < 48; $i++) {
                $recordedAt = $startTime->copy()->addMinutes($i * 30);

                // Add some random variation to simulate movement
                $latVariation = (mt_rand(-100, 100) / 1000); // ±0.1 degrees
                $lngVariation = (mt_rand(-100, 100) / 1000);

                DriverLocationTracking::create([
                    'driver_id' => $driver->id,
                    'latitude' => $baseLat + $latVariation,
                    'longitude' => $baseLng + $lngVariation,
                    'accuracy' => mt_rand(5, 50), // GPS accuracy in meters
                    'device_info' => 'iPhone ' . ['12', '13', '14', '15'][mt_rand(0, 3)] . ' Pro',
                    'metadata' => json_encode([
                        'speed' => mt_rand(0, 80), // km/h
                        'heading' => mt_rand(0, 359), // degrees
                        'altitude' => mt_rand(0, 100), // meters
                        'battery_level' => mt_rand(10, 100), // percentage
                        'network_type' => ['wifi', '4g', '5g'][mt_rand(0, 2)],
                    ]),
                    'recorded_at' => $recordedAt,
                ]);
            }
        }
    }
}
