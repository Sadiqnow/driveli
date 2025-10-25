<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Fleet;

class FleetSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Create owned fleet
            Fleet::firstOrCreate(
                ['company_id' => $company->id, 'name' => $company->name . ' Main Fleet'],
                [
                    'company_id' => $company->id,
                    'name' => $company->name . ' Main Fleet',
                    'description' => 'Primary fleet for daily operations',
                    'type' => 'owned',
                    'total_vehicles' => 0,
                    'active_vehicles' => 0,
                    'total_value' => rand(50000000, 200000000),
                    'manager_name' => 'Fleet Manager',
                    'manager_phone' => '+23480' . rand(10000000, 99999999),
                    'manager_email' => 'fleet@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                    'operating_regions' => [$company->state, 'Abuja'],
                    'status' => 'active',
                ]
            );

            // Create leased fleet if company is large
            if ($company->company_size === '1000+') {
                Fleet::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $company->name . ' Leased Fleet'],
                    [
                        'company_id' => $company->id,
                        'name' => $company->name . ' Leased Fleet',
                        'description' => 'Additional leased vehicles for peak periods',
                        'type' => 'leased',
                        'total_vehicles' => 0,
                        'active_vehicles' => 0,
                        'total_value' => rand(20000000, 100000000),
                        'manager_name' => 'Lease Manager',
                        'manager_phone' => '+23480' . rand(10000000, 99999999),
                        'manager_email' => 'lease@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                        'operating_regions' => [$company->state],
                        'status' => 'active',
                    ]
                );
            }

            // Create contracted fleet for some companies
            if (rand(0, 1)) {
                Fleet::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $company->name . ' Contracted Fleet'],
                    [
                        'company_id' => $company->id,
                        'name' => $company->name . ' Contracted Fleet',
                        'description' => 'Contracted vehicles for special operations',
                        'type' => 'contracted',
                        'total_vehicles' => 0,
                        'active_vehicles' => 0,
                        'total_value' => rand(10000000, 50000000),
                        'manager_name' => 'Contract Manager',
                        'manager_phone' => '+23480' . rand(10000000, 99999999),
                        'manager_email' => 'contract@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                        'operating_regions' => [$company->state, 'Lagos'],
                        'status' => 'active',
                    ]
                );
            }
        }

        $this->command->info('Fleets seeded successfully!');
    }
}
