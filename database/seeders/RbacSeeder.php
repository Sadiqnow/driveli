<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'id' => 1,
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'level' => 1,
                'is_active' => true,
                'meta' => json_encode(['system_role' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Administrative access with most permissions',
                'level' => 2,
                'is_active' => true,
                'parent_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Content moderation and user management',
                'level' => 3,
                'is_active' => true,
                'parent_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'agent',
                'display_name' => 'Agent',
                'description' => 'Customer support and basic operations',
                'level' => 4,
                'is_active' => true,
                'parent_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'driver',
                'display_name' => 'Driver',
                'description' => 'Driver access for profile management',
                'level' => 5,
                'is_active' => true,
                'parent_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);

        // Create permissions
        $permissions = [
            // Super Admin permissions
            ['name' => 'manage_superadmin', 'group_name' => 'superadmin'],
            ['name' => 'view_system_logs', 'group_name' => 'superadmin'],
            ['name' => 'manage_system_settings', 'group_name' => 'superadmin'],

            // Admin permissions
            ['name' => 'manage_admins', 'group_name' => 'admin'],
            ['name' => 'manage_roles', 'group_name' => 'admin'],
            ['name' => 'manage_permissions', 'group_name' => 'admin'],
            ['name' => 'view_audit_logs', 'group_name' => 'admin'],

            // Company permissions
            ['name' => 'manage_companies', 'group_name' => 'companies'],
            ['name' => 'verify_companies', 'group_name' => 'companies'],
            ['name' => 'view_company_requests', 'group_name' => 'companies'],
            ['name' => 'manage_company_requests', 'group_name' => 'companies'],

            // Driver permissions
            ['name' => 'manage_drivers', 'group_name' => 'drivers'],
            ['name' => 'verify_drivers', 'group_name' => 'drivers'],
            ['name' => 'view_driver_profiles', 'group_name' => 'drivers'],
            ['name' => 'manage_driver_kyc', 'group_name' => 'drivers'],

            // Matching permissions
            ['name' => 'create_matches', 'group_name' => 'matching'],
            ['name' => 'manage_matches', 'group_name' => 'matching'],
            ['name' => 'view_matches', 'group_name' => 'matching'],
            ['name' => 'manage_commissions', 'group_name' => 'matching'],

            // Template permissions
            ['name' => 'manage_templates', 'group_name' => 'templates'],
            ['name' => 'create_templates', 'group_name' => 'templates'],
            ['name' => 'view_templates', 'group_name' => 'templates'],

            // General permissions
            ['name' => 'view_dashboard', 'group_name' => 'general'],
            ['name' => 'export_data', 'group_name' => 'general'],
            ['name' => 'view_reports', 'group_name' => 'general'],
        ];

        DB::table('permissions')->insert($permissions);

        // Assign permissions to roles
        $rolePermissions = [
            // Super Admin - all permissions
            ['role_id' => 1, 'permission_id' => 1], // manage_superadmin
            ['role_id' => 1, 'permission_id' => 2], // view_system_logs
            ['role_id' => 1, 'permission_id' => 3], // manage_system_settings
            ['role_id' => 1, 'permission_id' => 4], // manage_admins
            ['role_id' => 1, 'permission_id' => 5], // manage_roles
            ['role_id' => 1, 'permission_id' => 6], // manage_permissions
            ['role_id' => 1, 'permission_id' => 7], // view_audit_logs
            ['role_id' => 1, 'permission_id' => 8], // manage_companies
            ['role_id' => 1, 'permission_id' => 9], // verify_companies
            ['role_id' => 1, 'permission_id' => 10], // view_company_requests
            ['role_id' => 1, 'permission_id' => 11], // manage_company_requests
            ['role_id' => 1, 'permission_id' => 12], // manage_drivers
            ['role_id' => 1, 'permission_id' => 13], // verify_drivers
            ['role_id' => 1, 'permission_id' => 14], // view_driver_profiles
            ['role_id' => 1, 'permission_id' => 15], // manage_driver_kyc
            ['role_id' => 1, 'permission_id' => 16], // create_matches
            ['role_id' => 1, 'permission_id' => 17], // manage_matches
            ['role_id' => 1, 'permission_id' => 18], // view_matches
            ['role_id' => 1, 'permission_id' => 19], // manage_commissions
            ['role_id' => 1, 'permission_id' => 20], // manage_templates
            ['role_id' => 1, 'permission_id' => 21], // create_templates
            ['role_id' => 1, 'permission_id' => 22], // view_templates
            ['role_id' => 1, 'permission_id' => 23], // view_dashboard
            ['role_id' => 1, 'permission_id' => 24], // export_data
            ['role_id' => 1, 'permission_id' => 25], // view_reports

            // Admin - most permissions except superadmin
            ['role_id' => 2, 'permission_id' => 4], // manage_admins
            ['role_id' => 2, 'permission_id' => 5], // manage_roles
            ['role_id' => 2, 'permission_id' => 6], // manage_permissions
            ['role_id' => 2, 'permission_id' => 7], // view_audit_logs
            ['role_id' => 2, 'permission_id' => 8], // manage_companies
            ['role_id' => 2, 'permission_id' => 9], // verify_companies
            ['role_id' => 2, 'permission_id' => 10], // view_company_requests
            ['role_id' => 2, 'permission_id' => 11], // manage_company_requests
            ['role_id' => 2, 'permission_id' => 12], // manage_drivers
            ['role_id' => 2, 'permission_id' => 13], // verify_drivers
            ['role_id' => 2, 'permission_id' => 14], // view_driver_profiles
            ['role_id' => 2, 'permission_id' => 15], // manage_driver_kyc
            ['role_id' => 2, 'permission_id' => 16], // create_matches
            ['role_id' => 2, 'permission_id' => 17], // manage_matches
            ['role_id' => 2, 'permission_id' => 18], // view_matches
            ['role_id' => 2, 'permission_id' => 19], // manage_commissions
            ['role_id' => 2, 'permission_id' => 20], // manage_templates
            ['role_id' => 2, 'permission_id' => 21], // create_templates
            ['role_id' => 2, 'permission_id' => 22], // view_templates
            ['role_id' => 2, 'permission_id' => 23], // view_dashboard
            ['role_id' => 2, 'permission_id' => 24], // export_data
            ['role_id' => 2, 'permission_id' => 25], // view_reports

            // Moderator - limited permissions
            ['role_id' => 3, 'permission_id' => 10], // view_company_requests
            ['role_id' => 3, 'permission_id' => 14], // view_driver_profiles
            ['role_id' => 3, 'permission_id' => 18], // view_matches
            ['role_id' => 3, 'permission_id' => 22], // view_templates
            ['role_id' => 3, 'permission_id' => 23], // view_dashboard

            // Agent - basic permissions
            ['role_id' => 4, 'permission_id' => 10], // view_company_requests
            ['role_id' => 4, 'permission_id' => 14], // view_driver_profiles
            ['role_id' => 4, 'permission_id' => 18], // view_matches
            ['role_id' => 4, 'permission_id' => 23], // view_dashboard

            // Driver - minimal permissions
            ['role_id' => 5, 'permission_id' => 23], // view_dashboard
        ];

        DB::table('role_permissions')->insert($rolePermissions);

        // Create default Super Admin user
        $superAdminId = DB::table('admin_users')->insertGetId([
            'name' => 'Super Admin',
            'email' => 'admin@drivelink.com',
            'password' => Hash::make('password123'),
            'phone' => '+2341234567890',
            'role' => 'Super Admin',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Super Admin role to the user
        DB::table('user_roles')->insert([
            'user_id' => $superAdminId,
            'role_id' => 1, // Super Admin role
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('RBAC system seeded successfully!');
        $this->command->info('Default Super Admin: admin@drivelink.com / password123');
    }
}
