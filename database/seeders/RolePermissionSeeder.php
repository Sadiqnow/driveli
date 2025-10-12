<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Create permissions
            $permissions = [
                // User Management
                ['name' => 'manage_users', 'display_name' => 'Manage Users', 'category' => 'User Management', 'resource' => 'users', 'action' => 'manage'],
                ['name' => 'view_users', 'display_name' => 'View Users', 'category' => 'User Management', 'resource' => 'users', 'action' => 'view'],
                ['name' => 'create_users', 'display_name' => 'Create Users', 'category' => 'User Management', 'resource' => 'users', 'action' => 'create'],
                ['name' => 'edit_users', 'display_name' => 'Edit Users', 'category' => 'User Management', 'resource' => 'users', 'action' => 'edit'],
                ['name' => 'delete_users', 'display_name' => 'Delete Users', 'category' => 'User Management', 'resource' => 'users', 'action' => 'delete'],

                // Role Management
                ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'category' => 'Role Management', 'resource' => 'roles', 'action' => 'manage'],
                ['name' => 'view_roles', 'display_name' => 'View Roles', 'category' => 'Role Management', 'resource' => 'roles', 'action' => 'view'],
                ['name' => 'create_roles', 'display_name' => 'Create Roles', 'category' => 'Role Management', 'resource' => 'roles', 'action' => 'create'],
                ['name' => 'edit_roles', 'display_name' => 'Edit Roles', 'category' => 'Role Management', 'resource' => 'roles', 'action' => 'edit'],
                ['name' => 'delete_roles', 'display_name' => 'Delete Roles', 'category' => 'Role Management', 'resource' => 'roles', 'action' => 'delete'],

                // Permission Management
                ['name' => 'manage_permissions', 'display_name' => 'Manage Permissions', 'category' => 'Permission Management', 'resource' => 'permissions', 'action' => 'manage'],
                ['name' => 'view_permissions', 'display_name' => 'View Permissions', 'category' => 'Permission Management', 'resource' => 'permissions', 'action' => 'view'],
                ['name' => 'create_permissions', 'display_name' => 'Create Permissions', 'category' => 'Permission Management', 'resource' => 'permissions', 'action' => 'create'],
                ['name' => 'edit_permissions', 'display_name' => 'Edit Permissions', 'category' => 'Permission Management', 'resource' => 'permissions', 'action' => 'edit'],
                ['name' => 'delete_permissions', 'display_name' => 'Delete Permissions', 'category' => 'Permission Management', 'resource' => 'permissions', 'action' => 'delete'],

                // Driver Management
                ['name' => 'manage_drivers', 'display_name' => 'Manage Drivers', 'category' => 'Driver Management', 'resource' => 'drivers', 'action' => 'manage'],
                ['name' => 'view_drivers', 'display_name' => 'View Drivers', 'category' => 'Driver Management', 'resource' => 'drivers', 'action' => 'view'],
                ['name' => 'create_drivers', 'display_name' => 'Create Drivers', 'category' => 'Driver Management', 'resource' => 'drivers', 'action' => 'create'],
                ['name' => 'edit_drivers', 'display_name' => 'Edit Drivers', 'category' => 'Driver Management', 'resource' => 'drivers', 'action' => 'edit'],
                ['name' => 'delete_drivers', 'display_name' => 'Delete Drivers', 'category' => 'Driver Management', 'resource' => 'drivers', 'action' => 'delete'],
                ['name' => 'verify_drivers', 'display_name' => 'Verify Drivers', 'category' => 'Driver Management', 'resource' => 'drivers', 'action' => 'verify'],

                // Company Management
                ['name' => 'manage_companies', 'display_name' => 'Manage Companies', 'category' => 'Company Management', 'resource' => 'companies', 'action' => 'manage'],
                ['name' => 'view_companies', 'display_name' => 'View Companies', 'category' => 'Company Management', 'resource' => 'companies', 'action' => 'view'],
                ['name' => 'create_companies', 'display_name' => 'Create Companies', 'category' => 'Company Management', 'resource' => 'companies', 'action' => 'create'],
                ['name' => 'edit_companies', 'display_name' => 'Edit Companies', 'category' => 'Company Management', 'resource' => 'companies', 'action' => 'edit'],
                ['name' => 'delete_companies', 'display_name' => 'Delete Companies', 'category' => 'Company Management', 'resource' => 'companies', 'action' => 'delete'],
                ['name' => 'verify_companies', 'display_name' => 'Verify Companies', 'category' => 'Company Management', 'resource' => 'companies', 'action' => 'verify'],

                // Request Management
                ['name' => 'manage_requests', 'display_name' => 'Manage Requests', 'category' => 'Request Management', 'resource' => 'requests', 'action' => 'manage'],
                ['name' => 'view_requests', 'display_name' => 'View Requests', 'category' => 'Request Management', 'resource' => 'requests', 'action' => 'view'],
                ['name' => 'create_requests', 'display_name' => 'Create Requests', 'category' => 'Request Management', 'resource' => 'requests', 'action' => 'create'],
                ['name' => 'edit_requests', 'display_name' => 'Edit Requests', 'category' => 'Request Management', 'resource' => 'requests', 'action' => 'edit'],
                ['name' => 'delete_requests', 'display_name' => 'Delete Requests', 'category' => 'Request Management', 'resource' => 'requests', 'action' => 'delete'],
                ['name' => 'approve_requests', 'display_name' => 'Approve Requests', 'category' => 'Request Management', 'resource' => 'requests', 'action' => 'approve'],

                // Matching System
                ['name' => 'manage_matching', 'display_name' => 'Manage Matching', 'category' => 'Matching System', 'resource' => 'matching', 'action' => 'manage'],
                ['name' => 'view_matching', 'display_name' => 'View Matching', 'category' => 'Matching System', 'resource' => 'matching', 'action' => 'view'],
                ['name' => 'create_matches', 'display_name' => 'Create Matches', 'category' => 'Matching System', 'resource' => 'matches', 'action' => 'create'],
                ['name' => 'edit_matches', 'display_name' => 'Edit Matches', 'category' => 'Matching System', 'resource' => 'matches', 'action' => 'edit'],
                ['name' => 'delete_matches', 'display_name' => 'Delete Matches', 'category' => 'Matching System', 'resource' => 'matches', 'action' => 'delete'],

                // Reports & Analytics
                ['name' => 'view_reports', 'display_name' => 'View Reports', 'category' => 'Reports & Analytics', 'resource' => 'reports', 'action' => 'view'],
                ['name' => 'export_reports', 'display_name' => 'Export Reports', 'category' => 'Reports & Analytics', 'resource' => 'reports', 'action' => 'export'],
                ['name' => 'view_analytics', 'display_name' => 'View Analytics', 'category' => 'Reports & Analytics', 'resource' => 'analytics', 'action' => 'view'],

                // Notifications
                ['name' => 'manage_notifications', 'display_name' => 'Manage Notifications', 'category' => 'Notifications', 'resource' => 'notifications', 'action' => 'manage'],
                ['name' => 'view_notifications', 'display_name' => 'View Notifications', 'category' => 'Notifications', 'resource' => 'notifications', 'action' => 'view'],
                ['name' => 'send_notifications', 'display_name' => 'Send Notifications', 'category' => 'Notifications', 'resource' => 'notifications', 'action' => 'send'],

                // System Administration
                ['name' => 'manage_system', 'display_name' => 'Manage System', 'category' => 'System Administration', 'resource' => 'system', 'action' => 'manage'],
                ['name' => 'view_system_logs', 'display_name' => 'View System Logs', 'category' => 'System Administration', 'resource' => 'logs', 'action' => 'view'],
                ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'category' => 'System Administration', 'resource' => 'settings', 'action' => 'manage'],
            ];

            foreach ($permissions as $permissionData) {
                Permission::updateOrCreate(
                    ['name' => $permissionData['name']],
                    array_merge($permissionData, ['is_active' => true])
                );
            }

            // Create roles
            $roles = [
                [
                    'name' => 'super_admin',
                    'display_name' => 'Super Admin',
                    'description' => 'Full system access with all permissions',
                    'level' => 100,
                    'permissions' => Permission::all()->pluck('name')->toArray() // All permissions
                ],
                [
                    'name' => 'admin',
                    'display_name' => 'Admin',
                    'description' => 'Administrative access with most permissions',
                    'level' => 80,
                    'permissions' => [
                        'manage_users', 'view_users', 'create_users', 'edit_users',
                        'manage_drivers', 'view_drivers', 'create_drivers', 'edit_drivers', 'verify_drivers',
                        'manage_companies', 'view_companies', 'create_companies', 'edit_companies', 'verify_companies',
                        'manage_requests', 'view_requests', 'create_requests', 'edit_requests', 'approve_requests',
                        'manage_matching', 'view_matching', 'create_matches', 'edit_matches',
                        'view_reports', 'export_reports', 'view_analytics',
                        'manage_notifications', 'view_notifications', 'send_notifications',
                        'view_system_logs'
                    ]
                ],
                [
                    'name' => 'manager',
                    'display_name' => 'Manager',
                    'description' => 'Management access with operational permissions',
                    'level' => 60,
                    'permissions' => [
                        'view_users', 'edit_users',
                        'manage_drivers', 'view_drivers', 'create_drivers', 'edit_drivers', 'verify_drivers',
                        'manage_companies', 'view_companies', 'create_companies', 'edit_companies', 'verify_companies',
                        'manage_requests', 'view_requests', 'create_requests', 'edit_requests', 'approve_requests',
                        'manage_matching', 'view_matching', 'create_matches', 'edit_matches',
                        'view_reports', 'export_reports', 'view_analytics',
                        'manage_notifications', 'view_notifications', 'send_notifications'
                    ]
                ],
                [
                    'name' => 'operator',
                    'display_name' => 'Operator',
                    'description' => 'Operational access for daily tasks',
                    'level' => 40,
                    'permissions' => [
                        'view_users',
                        'view_drivers', 'edit_drivers',
                        'view_companies', 'edit_companies',
                        'manage_requests', 'view_requests', 'create_requests', 'edit_requests', 'approve_requests',
                        'manage_matching', 'view_matching', 'create_matches', 'edit_matches',
                        'view_reports', 'view_analytics',
                        'view_notifications', 'send_notifications'
                    ]
                ],
                [
                    'name' => 'viewer',
                    'display_name' => 'Viewer',
                    'description' => 'Read-only access for monitoring',
                    'level' => 20,
                    'permissions' => [
                        'view_users',
                        'view_drivers',
                        'view_companies',
                        'view_requests',
                        'view_matching',
                        'view_reports', 'view_analytics',
                        'view_notifications'
                    ]
                ]
            ];

            foreach ($roles as $roleData) {
                $permissions = $roleData['permissions'];
                unset($roleData['permissions']);

                $role = Role::updateOrCreate(
                    ['name' => $roleData['name']],
                    array_merge($roleData, ['is_active' => true])
                );

                // Attach permissions to role
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
                $role->permissions()->sync($permissionIds);
            }

            // Create default super admin user
            $superAdmin = AdminUser::updateOrCreate(
                ['email' => 'superadmin@drivelink.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'phone' => '+2348012345678',
                    'role' => 'super_admin', // Legacy field
                    'status' => 'Active',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assign super admin role
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $superAdmin->roles()->syncWithoutDetaching([
                    $superAdminRole->id => [
                        'assigned_at' => now(),
                        'is_active' => true
                    ]
                ]);
            }

            DB::commit();

            $this->command->info('Role and Permission seeder completed successfully!');
            $this->command->info('Default super admin created: superadmin@drivelink.com / password');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding roles and permissions: ' . $e->getMessage());
            throw $e;
        }
    }
}
