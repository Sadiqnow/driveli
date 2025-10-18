<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'level' => 10,
                'is_active' => true
            ],
            [
                'name' => 'Admin',
                'display_name' => 'Administrator',
                'description' => 'High-level admin access for managing users and system',
                'level' => 8,
                'is_active' => true
            ],
            [
                'name' => 'Moderator',
                'display_name' => 'Moderator',
                'description' => 'Moderate users and content with limited admin access',
                'level' => 6,
                'is_active' => true
            ],
            [
                'name' => 'Agent',
                'display_name' => 'Field Agent',
                'description' => 'Field agent for customer interactions and support',
                'level' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Driver',
                'display_name' => 'Driver',
                'description' => 'Driver role for transportation services',
                'level' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Company',
                'display_name' => 'Company Representative',
                'description' => 'Company representative for business operations',
                'level' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Matching Officer',
                'display_name' => 'Matching Officer',
                'description' => 'Responsible for matching drivers with companies',
                'level' => 5,
                'is_active' => true
            ],
            [
                'name' => 'Verification Manager',
                'display_name' => 'Verification Manager',
                'description' => 'Manages verification processes and approvals',
                'level' => 7,
                'is_active' => true
            ]
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Roles seeded successfully.');
    }
}
