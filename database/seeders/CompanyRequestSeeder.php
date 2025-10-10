<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyRequest;
use App\Models\Company;

class CompanyRequestSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have companies first
        if (Company::count() == 0) {
            Company::create([
                'name' => 'ABC Logistics Ltd',
                'email' => 'admin@abclogistics.com',
                'phone' => '08012345678',
                'address' => '123 Business Street, Victoria Island, Lagos',
                'status' => 'active'
            ]);
            
            Company::create([
                'name' => 'XYZ Transport Services',
                'email' => 'info@xyztransport.com',
                'phone' => '08087654321',
                'address' => '456 Commerce Avenue, Wuse II, Abuja',
                'status' => 'active'
            ]);
            
            Company::create([
                'name' => 'Delta Delivery Co.',
                'email' => 'contact@deltadelivery.com',
                'phone' => '08098765432',
                'address' => '789 Industrial Road, Port Harcourt',
                'status' => 'active'
            ]);
        }
        
        $companies = Company::all();
        
        // Create company requests
        $requests = [
            [
                'company_id' => $companies->first()->id,
                'status' => 'pending',
                'description' => 'Need experienced driver for daily logistics operations. Must be reliable and punctual.',
                'location' => 'Lagos State',
                'job_type' => 'Full-time Delivery',
                'requirements' => 'Valid driver\'s license, 2+ years experience, clean driving record',
                'salary_range' => '150,000 - 200,000 per month'
            ],
            [
                'company_id' => $companies->first()->id,
                'status' => 'Active',
                'description' => 'Urgent: Executive driver needed for company CEO transport.',
                'location' => 'Lagos State',
                'job_type' => 'Executive Transport',
                'requirements' => 'Professional appearance, excellent driving skills, discretion required',
                'salary_range' => '250,000 - 300,000 per month'
            ],
            [
                'company_id' => $companies->count() > 1 ? $companies->get(1)->id : $companies->first()->id,
                'status' => 'pending',
                'description' => 'Part-time driver for weekend and evening deliveries.',
                'location' => 'Abuja',
                'job_type' => 'Part-time',
                'requirements' => 'Flexible schedule, own vehicle preferred',
                'salary_range' => '80,000 - 120,000 per month'
            ],
            [
                'company_id' => $companies->count() > 2 ? $companies->get(2)->id : $companies->first()->id,
                'status' => 'pending',
                'description' => 'Heavy duty truck driver for interstate cargo transport.',
                'location' => 'Port Harcourt',
                'job_type' => 'Heavy Duty',
                'requirements' => 'Heavy vehicle license, long-distance driving experience',
                'salary_range' => '200,000 - 280,000 per month'
            ],
            [
                'company_id' => $companies->count() > 1 ? $companies->get(1)->id : $companies->first()->id,
                'status' => 'Active',
                'description' => 'Company shuttle bus driver for employee transport.',
                'location' => 'Abuja',
                'job_type' => 'Public Transport',
                'requirements' => 'Bus driving experience, excellent people skills',
                'salary_range' => '180,000 - 220,000 per month'
            ],
            [
                'company_id' => $companies->first()->id,
                'status' => 'pending',
                'description' => 'Motorcycle dispatch rider for quick deliveries within the city.',
                'location' => 'Lagos State',
                'job_type' => 'Motorcycle Dispatch',
                'requirements' => 'Motorcycle license, knowledge of Lagos roads',
                'salary_range' => '100,000 - 150,000 per month'
            ]
        ];
        
        foreach ($requests as $requestData) {
            CompanyRequest::create($requestData);
        }
        
        $this->command->info('Company requests seeded successfully!');
    }
}