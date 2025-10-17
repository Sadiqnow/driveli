<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AdminUser;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'category' => 'user', 'resource' => 'users', 'action' => 'manage', 'description' => 'Create, edit, delete, and manage admin users'],
            ['name' => 'view_users', 'display_name' => 'View Users', 'category' => 'user', 'resource' => 'users', 'action' => 'view', 'description' => 'View admin users list and details'],
            ['name' => 'create_users', 'display_name' => 'Create Users', 'category' => 'user', 'resource' => 'users', 'action' => 'create', 'description' => 'Create new admin users'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'category' => 'user', 'resource' => 'users', 'action' => 'edit', 'description' => 'Edit admin user information'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users', 'category' => 'user', 'resource' => 'users', 'action' => 'delete', 'description' => 'Delete admin users'],

            // Role Management
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'category' => 'admin', 'resource' => 'roles', 'action' => 'manage', 'description' => 'Create, edit, delete, and manage roles'],
            ['name' => 'view_roles', 'display_name' => 'View Roles', 'category' => 'admin', 'resource' => 'roles', 'action' => 'view', 'description' => 'View roles and permissions'],
            ['name' => 'assign_roles', 'display_name' => 'Assign Roles', 'category' => 'admin', 'resource' => 'roles', 'action' => 'assign', 'description' => 'Assign roles to users'],

            // Permission Management
            ['name' => 'manage_permissions', 'display_name' => 'Manage Permissions', 'category' => 'admin', 'resource' => 'permissions', 'action' => 'manage', 'description' => 'Create, edit, delete, and manage permissions'],
            ['name' => 'view_permissions', 'display_name' => 'View Permissions', 'category' => 'admin', 'resource' => 'permissions', 'action' => 'view', 'description' => 'View permissions'],

            // Driver Management
            ['name' => 'manage_drivers', 'display_name' => 'Manage Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'manage', 'description' => 'Full driver management access'],
            ['name' => 'view_drivers', 'display_name' => 'View Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'view', 'description' => 'View drivers list and details'],
            ['name' => 'create_drivers', 'display_name' => 'Create Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'create', 'description' => 'Add new drivers'],
            ['name' => 'edit_drivers', 'display_name' => 'Edit Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'edit', 'description' => 'Edit driver information'],
            ['name' => 'delete_drivers', 'display_name' => 'Delete Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'delete', 'description' => 'Delete drivers'],
            ['name' => 'verify_drivers', 'display_name' => 'Verify Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'verify', 'description' => 'Verify driver documents and status'],
            ['name' => 'approve_drivers', 'display_name' => 'Approve Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'approve', 'description' => 'Approve or reject drivers'],
            ['name' => 'flag_drivers', 'display_name' => 'Flag Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'flag', 'description' => 'Flag drivers for review'],
            ['name' => 'suspend_drivers', 'display_name' => 'Suspend Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'suspend', 'description' => 'Suspend driver accounts'],
            ['name' => 'restore_drivers', 'display_name' => 'Restore Drivers', 'category' => 'driver', 'resource' => 'drivers', 'action' => 'restore', 'description' => 'Restore suspended drivers'],

            // Company Management
            ['name' => 'manage_companies', 'display_name' => 'Manage Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'manage', 'description' => 'Full company management access'],
            ['name' => 'view_companies', 'display_name' => 'View Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'view', 'description' => 'View companies list and details'],
            ['name' => 'create_companies', 'display_name' => 'Create Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'create', 'description' => 'Add new companies'],
            ['name' => 'edit_companies', 'display_name' => 'Edit Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'edit', 'description' => 'Edit company information'],
            ['name' => 'delete_companies', 'display_name' => 'Delete Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'delete', 'description' => 'Delete companies'],
            ['name' => 'verify_companies', 'display_name' => 'Verify Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'verify', 'description' => 'Verify company documents and status'],
            ['name' => 'approve_companies', 'display_name' => 'Approve Companies', 'category' => 'company', 'resource' => 'companies', 'action' => 'approve', 'description' => 'Approve or reject companies'],

            // Request Management
            ['name' => 'manage_requests', 'display_name' => 'Manage Requests', 'category' => 'system', 'resource' => 'requests', 'action' => 'manage', 'description' => 'Full request management access'],
            ['name' => 'view_requests', 'display_name' => 'View Requests', 'category' => 'system', 'resource' => 'requests', 'action' => 'view', 'description' => 'View requests list and details'],
            ['name' => 'create_requests', 'display_name' => 'Create Requests', 'category' => 'system', 'resource' => 'requests', 'action' => 'create', 'description' => 'Create new requests'],
            ['name' => 'edit_requests', 'display_name' => 'Edit Requests', 'category' => 'system', 'resource' => 'requests', 'action' => 'edit', 'description' => 'Edit request information'],
            ['name' => 'delete_requests', 'display_name' => 'Delete Requests', 'category' => 'system', 'resource' => 'requests', 'action' => 'delete', 'description' => 'Delete requests'],
            ['name' => 'approve_requests', 'display_name' => 'Approve Requests', 'category' => 'system', 'resource' => 'requests', 'action' => 'approve', 'description' => 'Approve or reject requests'],

            // Matching System
            ['name' => 'manage_matching', 'display_name' => 'Manage Matching', 'category' => 'system', 'resource' => 'matching', 'action' => 'manage', 'description' => 'Full matching system access'],
            ['name' => 'view_matches', 'display_name' => 'View Matches', 'category' => 'system', 'resource' => 'matches', 'action' => 'view', 'description' => 'View driver-request matches'],
            ['name' => 'create_matches', 'display_name' => 'Create Matches', 'category' => 'system', 'resource' => 'matches', 'action' => 'create', 'description' => 'Create driver-request matches'],

            // Reports
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'category' => 'report', 'resource' => 'reports', 'action' => 'view', 'description' => 'View system reports and analytics'],
            ['name' => 'create_reports', 'display_name' => 'Create Reports', 'category' => 'report', 'resource' => 'reports', 'action' => 'create', 'description' => 'Generate custom reports'],
            ['name' => 'export_reports', 'display_name' => 'Export Reports', 'category' => 'report', 'resource' => 'reports', 'action' => 'export', 'description' => 'Export reports to various formats'],

            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'category' => 'system', 'resource' => 'dashboard', 'action' => 'view', 'description' => 'Access to admin dashboard'],

            // System Management
            ['name' => 'manage_system', 'display_name' => 'Manage System', 'category' => 'system', 'resource' => 'system', 'action' => 'manage', 'description' => 'Full system administration access'],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'category' => 'system', 'resource' => 'settings', 'action' => 'manage', 'description' => 'Manage system settings'],
            ['name' => 'view_logs', 'display_name' => 'View Logs', 'category' => 'system', 'resource' => 'logs', 'action' => 'view', 'description' => 'View system logs'],
            ['name' => 'manage_notifications', 'display_name' => 'Manage Notifications', 'category' => 'system', 'resource' => 'notifications', 'action' => 'manage', 'description' => 'Send and manage notifications'],

            // Commission Management
            ['name' => 'manage_commissions', 'display_name' => 'Manage Commissions', 'category' => 'system', 'resource' => 'commissions', 'action' => 'manage', 'description' => 'Manage commission payments and rates'],
            ['name' => 'view_commissions', 'display_name' => 'View Commissions', 'category' => 'system', 'resource' => 'commissions', 'action' => 'view', 'description' => 'View commission information'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        $this->command->info('Permissions created successfully!');

        // Create Roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access including user management, system settings, and all administrative functions.',
                'level' => 100,
                'is_active' => true
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Can manage drivers, companies, requests, and view reports. Cannot manage other admin users unless specifically granted.',
                'level' => 10,
                'is_active' => true
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Can verify drivers and companies, moderate requests, but has limited management capabilities.',
                'level' => 5,
                'is_active' => true
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to drivers, companies, requests, and reports. Cannot make changes.',
                'level' => 1,
                'is_active' => true
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles created successfully!');

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Create default admin user if none exists
        $this->createDefaultAdminUser();

        $this->command->info('RBAC system setup completed!');
    }

    private function assignPermissionsToRoles()
    {
        // Super Admin gets all permissions
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::where('is_active', true)->get();
            foreach ($allPermissions as $permission) {
                $superAdminRole->givePermissionTo($permission);
            }
            $this->command->info('Super Admin permissions assigned!');
        }

        // Admin permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminPermissions = [
                'view_dashboard',
                'manage_drivers', 'view_drivers', 'create_drivers', 'edit_drivers', 'verify_drivers', 'approve_drivers', 'flag_drivers', 'suspend_drivers', 'restore_drivers',
                'manage_companies', 'view_companies', 'create_companies', 'edit_companies', 'verify_companies', 'approve_companies',
                'manage_requests', 'view_requests', 'create_requests', 'edit_requests', 'approve_requests',
                'manage_matching', 'view_matches', 'create_matches',
                'view_reports', 'create_reports', 'export_reports',
                'manage_commissions', 'view_commissions',
                'manage_notifications'
            ];

            foreach ($adminPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $adminRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Admin permissions assigned!');
        }

        // Moderator permissions
        $moderatorRole = Role::where('name', 'moderator')->first();
        if ($moderatorRole) {
            $moderatorPermissions = [
                'view_dashboard',
                'view_drivers', 'verify_drivers', 'approve_drivers', 'flag_drivers',
                'view_companies', 'verify_companies', 'approve_companies',
                'view_requests', 'approve_requests',
                'view_matches',
                'view_reports',
                'view_commissions'
            ];

            foreach ($moderatorPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $moderatorRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Moderator permissions assigned!');
        }

        // Viewer permissions
        $viewerRole = Role::where('name', 'viewer')->first();
        if ($viewerRole) {
            $viewerPermissions = [
                'view_dashboard',
                'view_drivers',
                'view_companies',
                'view_requests',
                'view_matches',
                'view_reports',
                'view_commissions'
            ];

            foreach ($viewerPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $viewerRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Viewer permissions assigned!');
        }
    }

    private function createDefaultAdminUser()
    {
        // Check if any super admin exists
        $existingSuperAdmin = AdminUser::where('role', 'Super Admin')->first();

        if (!$existingSuperAdmin) {
            $superAdminRole = Role::where('name', 'super_admin')->first();

            $defaultAdmin = AdminUser::create([
                'name' => 'Super Administrator',
                'email' => 'admin@drivelink.com',
                'password' => 'admin123', // Will be hashed by mutator
                'role' => 'Super Admin',
                'status' => 'Active',
                'phone' => '+234 800 000 0000',
                'permissions' => []
            ]);

            if ($superAdminRole) {
                // Assign super admin role
                $defaultAdmin->assignRole($superAdminRole);
            }

            $this->command->warn('Default Super Admin created:');
            $this->command->warn('Email: admin@drivelink.com');
            $this->command->warn('Password: admin123');
            $this->command->warn('Please change these credentials immediately!');
        }
    }
}
