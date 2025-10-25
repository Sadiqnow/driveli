<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyMember;

class CompanyMemberSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Create owner
            CompanyMember::firstOrCreate(
                ['email' => 'owner@' . strtolower(str_replace(' ', '', $company->name)) . '.com'],
                [
                    'company_id' => $company->id,
                    'first_name' => 'John',
                    'last_name' => 'Owner',
                    'email' => 'owner@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                    'phone' => '+2348012345678',
                    'position' => 'Owner',
                    'department' => 'Management',
                    'role' => 'admin',
                    'is_active' => true,
                    'last_login_at' => now(),
                ]
            );

            // Create admin
            CompanyMember::firstOrCreate(
                ['email' => 'admin@' . strtolower(str_replace(' ', '', $company->name)) . '.com'],
                [
                    'company_id' => $company->id,
                    'first_name' => 'Jane',
                    'last_name' => 'Admin',
                    'email' => 'admin@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                    'phone' => '+2348023456789',
                    'position' => 'Administrator',
                    'department' => 'Operations',
                    'role' => 'admin',
                    'is_active' => true,
                    'last_login_at' => now()->subDays(rand(1, 30)),
                ]
            );

            // Create manager
            CompanyMember::firstOrCreate(
                ['email' => 'manager@' . strtolower(str_replace(' ', '', $company->name)) . '.com'],
                [
                    'company_id' => $company->id,
                    'first_name' => 'Mike',
                    'last_name' => 'Manager',
                    'email' => 'manager@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                    'phone' => '+2348034567890',
                    'position' => 'Fleet Manager',
                    'department' => 'Fleet Operations',
                    'role' => 'manager',
                    'is_active' => true,
                    'last_login_at' => now()->subDays(rand(1, 30)),
                ]
            );

            // Create employees
            for ($i = 1; $i <= 3; $i++) {
                CompanyMember::firstOrCreate(
                    ['email' => 'employee' . $i . '@' . strtolower(str_replace(' ', '', $company->name)) . '.com'],
                    [
                        'company_id' => $company->id,
                        'first_name' => 'Employee',
                        'last_name' => 'Number' . $i,
                        'email' => 'employee' . $i . '@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                        'phone' => '+23480' . rand(10000000, 99999999),
                        'position' => 'Staff',
                        'department' => 'Operations',
                        'role' => 'employee',
                        'is_active' => true,
                        'last_login_at' => now()->subDays(rand(1, 60)),
                    ]
                );
            }
        }

        $this->command->info('Company members seeded successfully!');
    }
}
