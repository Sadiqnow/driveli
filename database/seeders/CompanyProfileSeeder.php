<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyProfile;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            CompanyProfile::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'mission_statement' => 'To provide exceptional logistics and transportation services.',
                    'vision_statement' => 'To be the leading transportation partner in Nigeria.',
                    'core_values' => json_encode(['Integrity', 'Excellence', 'Safety', 'Innovation']),
                    'social_media_links' => json_encode([
                        'facebook' => 'https://facebook.com/' . strtolower(str_replace(' ', '', $company->name)),
                        'twitter' => 'https://twitter.com/' . strtolower(str_replace(' ', '', $company->name)),
                        'linkedin' => 'https://linkedin.com/company/' . strtolower(str_replace(' ', '-', $company->name))
                    ]),
                    'facebook_url' => 'https://facebook.com/' . strtolower(str_replace(' ', '', $company->name)),
                    'twitter_url' => 'https://twitter.com/' . strtolower(str_replace(' ', '', $company->name)),
                    'linkedin_url' => 'https://linkedin.com/company/' . strtolower(str_replace(' ', '-', $company->name)),
                    'company_history' => 'Founded in ' . rand(1990, 2020) . ', ' . $company->name . ' has been a pioneer in the industry.',
                    'certifications' => json_encode(['ISO 9001', 'ISO 14001']),
                    'awards' => json_encode(['Best Logistics Company 2023']),
                    'employee_count' => rand(50, 5000),
                    'annual_revenue' => rand(1000000, 100000000),
                    'headquarters_location' => $company->address,
                    'branch_locations' => json_encode([$company->state, 'Abuja', 'Kano'])
                ]
            );
        }

        $this->command->info('Company profiles seeded successfully!');
    }
}
