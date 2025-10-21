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
        // Define roles with hierarchy: SuperAdmin → Admin → Moderator → Agent → Driver → Company → Matching Officer → Verification Manager
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'level' => 100,
                'parent_id' => null, // Root level
                'is_active' => true
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'High-level admin access for managing users and system',
                'level' => 90,
                'parent_id' => null, // Will be set to super_admin after creation
                'is_active' => true
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Moderate users and content with limited admin access',
                'level' => 50,
                'parent_id' => null, // Will be set to admin after creation
                'is_active' => true
            ],
            [
                'name' => 'agent',
                'display_name' => 'Field Agent',
                'description' => 'Field agent for customer interactions and support',
                'level' => 30,
                'parent_id' => null, // Will be set to moderator after creation
                'is_active' => true
            ],
            [
                'name' => 'driver',
                'display_name' => 'Driver',
                'description' => 'Driver role for transportation services',
                'level' => 20,
                'parent_id' => null, // Will be set to agent after creation
                'is_active' => true
            ],
            [
                'name' => 'company',
                'display_name' => 'Company Representative',
                'description' => 'Company representative for business operations',
                'level' => 20,
                'parent_id' => null, // Will be set to agent after creation (same level as driver)
                'is_active' => true
            ],
            [
                'name' => 'matching_officer',
                'display_name' => 'Matching Officer',
                'description' => 'Responsible for matching drivers with companies',
                'level' => 40,
                'parent_id' => null, // Will be set to moderator after creation
                'is_active' => true
            ],
            [
                'name' => 'verification_manager',
                'display_name' => 'Verification Manager',
                'description' => 'Manages verification processes and approvals',
                'level' => 60,
                'parent_id' => null, // Will be set to admin after creation
                'is_active' => true
            ]
        ];

        // First pass: Create all roles without parent_id
        $createdRoles = [];
        foreach ($roles as $roleData) {
            $role = DB::table('roles')->updateOrInsert(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'level' => $roleData['level'],
                    'parent_id' => null, // Set to null initially
                    'is_active' => $roleData['is_active'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Get the created/updated role ID
            $createdRole = DB::table('roles')->where('name', $roleData['name'])->first();
            $createdRoles[$roleData['name']] = $createdRole->id;
        }

        // Second pass: Update parent_id relationships
        $hierarchy = [
            'admin' => 'super_admin',
            'verification_manager' => 'admin',
            'moderator' => 'admin',
            'matching_officer' => 'moderator',
            'agent' => 'moderator',
            'driver' => 'agent',
            'company' => 'agent',
        ];

        foreach ($hierarchy as $child => $parent) {
            if (isset($createdRoles[$child]) && isset($createdRoles[$parent])) {
                DB::table('roles')
                    ->where('id', $createdRoles[$child])
                    ->update(['parent_id' => $createdRoles[$parent]]);
            }
        }

        $this->command->info('Roles seeded successfully with hierarchy.');
    }
}
