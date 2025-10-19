<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating unified RBAC system...');

        // Create permissions first
        $permissions = $this->getPermissionsData();
        $createdPermissions = [];

        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
            $createdPermissions[$permission->name] = $permission;
            $this->command->info("Created permission: {$permission->name}");
        }

        // Create roles
        $roles = $this->getRolesData();
        $createdRoles = [];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            $createdRoles[$role->name] = $role;
            $this->command->info("Created role: {$role->name}");
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles($createdRoles, $createdPermissions);

        $this->command->info('Unified RBAC system created successfully!');
    }

    /**
     * Get permissions data
     */
    private function getPermissionsData(): array
    {
        return [
            // System Management
            [
                'name' => 'manage_system',
                'display_name' => 'Manage System',
                'description' => 'Full system management access',
                'category' => 'system',
                'resource' => 'system',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'view_dashboard',
                'display_name' => 'View Dashboard',
                'description' => 'Access to admin dashboard',
                'category' => 'system',
                'resource' => 'dashboard',
                'action' => 'view',
                'is_active' => true
            ],

            // User Management
            [
                'name' => 'manage_users',
                'display_name' => 'Manage Users',
                'description' => 'Create, edit, delete admin users',
                'category' => 'user',
                'resource' => 'users',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'view_users',
                'display_name' => 'View Users',
                'description' => 'View admin users list',
                'category' => 'user',
                'resource' => 'users',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'create_users',
                'display_name' => 'Create Users',
                'description' => 'Create new admin users',
                'category' => 'user',
                'resource' => 'users',
                'action' => 'create',
                'is_active' => true
            ],
            [
                'name' => 'edit_users',
                'display_name' => 'Edit Users',
                'description' => 'Edit existing admin users',
                'category' => 'user',
                'resource' => 'users',
                'action' => 'edit',
                'is_active' => true
            ],
            [
                'name' => 'delete_users',
                'display_name' => 'Delete Users',
                'description' => 'Delete admin users',
                'category' => 'user',
                'resource' => 'users',
                'action' => 'delete',
                'is_active' => true
            ],

            // Role Management
            [
                'name' => 'manage_roles',
                'display_name' => 'Manage Roles',
                'description' => 'Create, edit, delete roles',
                'category' => 'admin',
                'resource' => 'roles',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'view_roles',
                'display_name' => 'View Roles',
                'description' => 'View roles list',
                'category' => 'admin',
                'resource' => 'roles',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'create_roles',
                'display_name' => 'Create Roles',
                'description' => 'Create new roles',
                'category' => 'admin',
                'resource' => 'roles',
                'action' => 'create',
                'is_active' => true
            ],
            [
                'name' => 'edit_roles',
                'display_name' => 'Edit Roles',
                'description' => 'Edit existing roles',
                'category' => 'admin',
                'resource' => 'roles',
                'action' => 'edit',
                'is_active' => true
            ],
            [
                'name' => 'delete_roles',
                'display_name' => 'Delete Roles',
                'description' => 'Delete roles',
                'category' => 'admin',
                'resource' => 'roles',
                'action' => 'delete',
                'is_active' => true
            ],
            [
                'name' => 'assign_roles',
                'display_name' => 'Assign Roles',
                'description' => 'Assign roles to users',
                'category' => 'admin',
                'resource' => 'roles',
                'action' => 'assign',
                'is_active' => true
            ],

            // Permission Management
            [
                'name' => 'manage_permissions',
                'display_name' => 'Manage Permissions',
                'description' => 'Create, edit, delete permissions',
                'category' => 'admin',
                'resource' => 'permissions',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'view_permissions',
                'display_name' => 'View Permissions',
                'description' => 'View permissions list',
                'category' => 'admin',
                'resource' => 'permissions',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'create_permissions',
                'display_name' => 'Create Permissions',
                'description' => 'Create new permissions',
                'category' => 'admin',
                'resource' => 'permissions',
                'action' => 'create',
                'is_active' => true
            ],
            [
                'name' => 'edit_permissions',
                'display_name' => 'Edit Permissions',
                'description' => 'Edit existing permissions',
                'category' => 'admin',
                'resource' => 'permissions',
                'action' => 'edit',
                'is_active' => true
            ],
            [
                'name' => 'assign_permissions',
                'display_name' => 'Assign Permissions',
                'description' => 'Assign permissions to roles',
                'category' => 'admin',
                'resource' => 'permissions',
                'action' => 'assign',
                'is_active' => true
            ],

            // Driver Management
            [
                'name' => 'view_drivers',
                'display_name' => 'View Drivers',
                'description' => 'View drivers list and details',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'create_drivers',
                'display_name' => 'Create Drivers',
                'description' => 'Create new driver profiles',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'create',
                'is_active' => true
            ],
            [
                'name' => 'edit_drivers',
                'display_name' => 'Edit Drivers',
                'description' => 'Edit driver profiles and information',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'edit',
                'is_active' => true
            ],
            [
                'name' => 'delete_drivers',
                'display_name' => 'Delete Drivers',
                'description' => 'Delete driver profiles',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'delete',
                'is_active' => true
            ],
            [
                'name' => 'approve_drivers',
                'display_name' => 'Approve Drivers',
                'description' => 'Approve or reject driver applications',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'approve',
                'is_active' => true
            ],
            [
                'name' => 'verify_drivers',
                'display_name' => 'Verify Drivers',
                'description' => 'Verify driver documents and credentials',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'verify',
                'is_active' => true
            ],
            [
                'name' => 'manage_drivers',
                'display_name' => 'Manage Drivers',
                'description' => 'Full driver management including verification and approval',
                'category' => 'driver',
                'resource' => 'drivers',
                'action' => 'manage',
                'is_active' => true
            ],

            // Company Management
            [
                'name' => 'view_companies',
                'display_name' => 'View Companies',
                'description' => 'View company list and details',
                'category' => 'company',
                'resource' => 'companies',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'create_companies',
                'display_name' => 'Create Companies',
                'description' => 'Create new company profiles',
                'category' => 'company',
                'resource' => 'companies',
                'action' => 'create',
                'is_active' => true
            ],
            [
                'name' => 'edit_companies',
                'display_name' => 'Edit Companies',
                'description' => 'Edit company profiles and information',
                'category' => 'company',
                'resource' => 'companies',
                'action' => 'edit',
                'is_active' => true
            ],
            [
                'name' => 'delete_companies',
                'display_name' => 'Delete Companies',
                'description' => 'Delete company profiles',
                'category' => 'company',
                'resource' => 'companies',
                'action' => 'delete',
                'is_active' => true
            ],
            [
                'name' => 'approve_companies',
                'display_name' => 'Approve Companies',
                'description' => 'Approve or reject company applications',
                'category' => 'company',
                'resource' => 'companies',
                'action' => 'approve',
                'is_active' => true
            ],

            // Report Management
            [
                'name' => 'view_reports',
                'display_name' => 'View Reports',
                'description' => 'Access reporting dashboard and analytics',
                'category' => 'report',
                'resource' => 'reports',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'create_reports',
                'display_name' => 'Create Reports',
                'description' => 'Create custom reports and analytics',
                'category' => 'report',
                'resource' => 'reports',
                'action' => 'create',
                'is_active' => true
            ],
            [
                'name' => 'export_reports',
                'display_name' => 'Export Reports',
                'description' => 'Export reports to various formats',
                'category' => 'report',
                'resource' => 'reports',
                'action' => 'export',
                'is_active' => true
            ],

            // Additional permissions
            [
                'name' => 'manage_requests',
                'display_name' => 'Manage Requests',
                'description' => 'Manage company requests and applications',
                'category' => 'company',
                'resource' => 'requests',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'manage_matching',
                'display_name' => 'Manage Matching',
                'description' => 'Manage driver-company matching system',
                'category' => 'system',
                'resource' => 'matching',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'manage_verification',
                'display_name' => 'Manage Verification',
                'description' => 'Manage document verification and approval processes',
                'category' => 'driver',
                'resource' => 'verification',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'manage_commissions',
                'display_name' => 'Manage Commissions',
                'description' => 'Manage commission payments and calculations',
                'category' => 'system',
                'resource' => 'commissions',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'manage_superadmin',
                'display_name' => 'Manage Super Admin',
                'description' => 'Super admin system management functions',
                'category' => 'system',
                'resource' => 'superadmin',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'view_audit_logs',
                'display_name' => 'View Audit Logs',
                'description' => 'View system audit logs and activities',
                'category' => 'system',
                'resource' => 'audit_logs',
                'action' => 'view',
                'is_active' => true
            ],
            [
                'name' => 'manage_notifications',
                'display_name' => 'Manage Notifications',
                'description' => 'Send and manage system notifications',
                'category' => 'system',
                'resource' => 'notifications',
                'action' => 'manage',
                'is_active' => true
            ],
            [
                'name' => 'view_permission_analytics',
                'display_name' => 'View Permission Analytics',
                'description' => 'Access to permission and role analytics dashboard',
                'category' => 'system',
                'resource' => 'analytics',
                'action' => 'view',
                'is_active' => true
            ],
        ];
    }

    /**
     * Get roles data
     */
    private function getRolesData(): array
    {
        return [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'level' => Role::LEVEL_SUPER_ADMIN,
                'is_active' => true
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Administrative access to most system functions',
                'level' => Role::LEVEL_ADMIN,
                'is_active' => true
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Content moderation and user management',
                'level' => Role::LEVEL_MODERATOR,
                'is_active' => true
            ],
            [
                'name' => 'agent',
                'display_name' => 'Agent',
                'description' => 'Customer support and basic administrative tasks',
                'level' => Role::LEVEL_AGENT,
                'is_active' => true
            ],
            [
                'name' => 'driver',
                'display_name' => 'Driver',
                'description' => 'Driver portal access',
                'level' => Role::LEVEL_DRIVER,
                'is_active' => true
            ],
            [
                'name' => 'company',
                'display_name' => 'Company',
                'description' => 'Company portal access',
                'level' => Role::LEVEL_COMPANY,
                'is_active' => true
            ],
            [
                'name' => 'matching_officer',
                'display_name' => 'Matching Officer',
                'description' => 'Driver-company matching specialist',
                'level' => Role::LEVEL_MATCHING_OFFICER,
                'is_active' => true
            ],
            [
                'name' => 'verification_manager',
                'display_name' => 'Verification Manager',
                'description' => 'Document verification and approval management',
                'level' => Role::LEVEL_VERIFICATION_MANAGER,
                'is_active' => true
            ],
        ];
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(array $roles, array $permissions): void
    {
        // Super Admin - All permissions
        $superAdminPermissions = collect($permissions)->pluck('id')->toArray();
        $roles['super_admin']->permissions()->sync($superAdminPermissions);
        $this->command->info("Assigned " . count($superAdminPermissions) . " permissions to Super Admin");

        // Admin - Most permissions except super admin specific
        $adminPermissions = [
            'manage_users', 'view_users', 'create_users', 'edit_users', 'delete_users',
            'manage_roles', 'view_roles', 'create_roles', 'edit_roles', 'assign_roles',
            'manage_permissions', 'view_permissions', 'create_permissions', 'edit_permissions', 'assign_permissions',
            'view_drivers', 'create_drivers', 'edit_drivers', 'delete_drivers', 'approve_drivers', 'verify_drivers', 'manage_drivers',
            'view_companies', 'create_companies', 'edit_companies', 'delete_companies', 'approve_companies',
            'view_reports', 'create_reports', 'export_reports',
            'manage_requests', 'manage_matching', 'manage_verification', 'manage_commissions',
            'view_dashboard', 'view_audit_logs', 'manage_notifications', 'view_permission_analytics'
        ];
        $roles['admin']->permissions()->sync(collect($adminPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($adminPermissions) . " permissions to Admin");

        // Moderator - Limited user and content management
        $moderatorPermissions = [
            'view_users', 'view_drivers', 'view_companies', 'view_reports',
            'manage_requests', 'manage_matching', 'view_dashboard'
        ];
        $roles['moderator']->permissions()->sync(collect($moderatorPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($moderatorPermissions) . " permissions to Moderator");

        // Agent - Basic support functions
        $agentPermissions = [
            'view_users', 'view_drivers', 'view_companies', 'view_reports',
            'manage_requests', 'view_dashboard'
        ];
        $roles['agent']->permissions()->sync(collect($agentPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($agentPermissions) . " permissions to Agent");

        // Driver - Basic driver functions
        $driverPermissions = [
            'view_dashboard'
        ];
        $roles['driver']->permissions()->sync(collect($driverPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($driverPermissions) . " permissions to Driver");

        // Company - Basic company functions
        $companyPermissions = [
            'view_dashboard', 'manage_requests'
        ];
        $roles['company']->permissions()->sync(collect($companyPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($companyPermissions) . " permissions to Company");

        // Matching Officer - Specialized matching functions
        $matchingOfficerPermissions = [
            'view_users', 'view_drivers', 'view_companies', 'view_reports',
            'manage_requests', 'manage_matching', 'view_dashboard'
        ];
        $roles['matching_officer']->permissions()->sync(collect($matchingOfficerPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($matchingOfficerPermissions) . " permissions to Matching Officer");

        // Verification Manager - Document verification functions
        $verificationManagerPermissions = [
            'view_users', 'view_drivers', 'view_companies', 'view_reports',
            'manage_verification', 'verify_drivers', 'approve_drivers', 'view_dashboard'
        ];
        $roles['verification_manager']->permissions()->sync(collect($verificationManagerPermissions)->map(fn($name) => $permissions[$name]->id)->toArray());
        $this->command->info("Assigned " . count($verificationManagerPermissions) . " permissions to Verification Manager");
    }
}
