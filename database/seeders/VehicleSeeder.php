<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fleet;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        $fleets = Fleet::all();

        $vehicleTypes = ['sedan', 'suv', 'truck', 'van', 'motorcycle', 'bus'];
        $makes = ['Toyota', 'Honda', 'Ford', 'Mercedes', 'Nissan', 'Volkswagen', 'Hyundai'];
        $models = ['Camry', 'Accord', 'Explorer', 'E-Class', 'Altima', 'Golf', 'Sonata'];
        $colors = ['White', 'Black', 'Silver', 'Blue', 'Red', 'Green'];

        foreach ($fleets as $fleet) {
            $vehicleCount = rand(5, 15); // 5-15 vehicles per fleet

            for ($i = 1; $i <= $vehicleCount; $i++) {
                $vehicleType = $vehicleTypes[array_rand($vehicleTypes)];
                $make = $makes[array_rand($makes)];
                $model = $models[array_rand($models)];
                $year = rand(2015, 2023);
                $color = $colors[array_rand($colors)];

                Vehicle::firstOrCreate(
                    ['registration_number' => strtoupper(substr($fleet->company->name, 0, 3)) . '-' . $fleet->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                    [
                        'fleet_id' => $fleet->id,
                        'registration_number' => strtoupper(substr($fleet->company->name, 0, 3)) . '-' . $fleet->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'make' => $make,
                        'model' => $model,
                        'year' => $year,
                        'color' => $color,
                        'vin' => strtoupper(substr(md5(uniqid()), 0, 17)),
                        'engine_number' => strtoupper(substr(md5(uniqid()), 0, 10)),
                        'chassis_number' => strtoupper(substr(md5(uniqid()), 0, 12)),
                        'vehicle_type' => $vehicleType,
                        'seating_capacity' => $this->getSeatingCapacity($vehicleType),
                        'purchase_price' => rand(2000000, 15000000),
                        'purchase_date' => now()->subYears(rand(1, 5)),
                        'current_value' => rand(1500000, 12000000),
                        'insurance_expiry' => now()->addYears(rand(0, 1)),
                        'insurance_provider' => ['Leadway', 'AIICO', 'Custodian'][array_rand(['Leadway', 'AIICO', 'Custodian'])],
                        'road_worthiness_expiry' => now()->addMonths(rand(1, 12)),
                        'mileage' => rand(10000, 200000),
                        'status' => ['active', 'active', 'active', 'maintenance'][array_rand(['active', 'active', 'active', 'maintenance'])],
                        'notes' => 'Regular maintenance vehicle',
                        'features' => json_encode(['AC', 'Radio', 'GPS']),
                    ]
                );
            }

            // Update fleet vehicle counts
            $fleet->updateVehicleCounts();
        }

        $this->command->info('Vehicles seeded successfully!');
    }

    private function getSeatingCapacity($vehicleType)
    {
        switch ($vehicleType) {
            case 'sedan':
                return rand(4, 5);
            case 'suv':
                return rand(5, 7);
            case 'truck':
                return rand(2, 3);
            case 'van':
                return rand(8, 15);
            case 'motorcycle':
                return 1;
            case 'bus':
                return rand(20, 50);
            default:
                return 4;
        }
    }
}
