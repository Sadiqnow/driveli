<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // System Management
            [
                'name' => Permission::MANAGE_SYSTEM,
                'display_name' => 'Manage System',
                'description' => 'Full system management access',
                'category' => Permission::CATEGORY_SYSTEM,
                'resource' => 'system',
                'action' => Permission::ACTION_MANAGE
            ],
            [
                'name' => Permission::VIEW_DASHBOARD,
                'display_name' => 'View Dashboard',
                'description' => 'Access to admin dashboard',
                'category' => Permission::CATEGORY_SYSTEM,
                'resource' => 'dashboard',
                'action' => Permission::ACTION_VIEW
            ],
            
            // User Management
            [
                'name' => Permission::MANAGE_USERS,
                'display_name' => 'Manage Users',
                'description' => 'Create, edit, delete admin users',
                'category' => Permission::CATEGORY_USER,
                'resource' => 'users',
                'action' => Permission::ACTION_MANAGE
            ],
            [
                'name' => 'view_users',
                'display_name' => 'View Users',
                'description' => 'View admin users list',
                'category' => Permission::CATEGORY_USER,
                'resource' => 'users',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => 'create_users',
                'display_name' => 'Create Users',
                'description' => 'Create new admin users',
                'category' => Permission::CATEGORY_USER,
                'resource' => 'users',
                'action' => Permission::ACTION_CREATE
            ],
            [
                'name' => 'edit_users',
                'display_name' => 'Edit Users',
                'description' => 'Edit existing admin users',
                'category' => Permission::CATEGORY_USER,
                'resource' => 'users',
                'action' => Permission::ACTION_EDIT
            ],
            [
                'name' => 'delete_users',
                'display_name' => 'Delete Users',
                'description' => 'Delete admin users',
                'category' => Permission::CATEGORY_USER,
                'resource' => 'users',
                'action' => Permission::ACTION_DELETE
            ],

            // Role Management
            [
                'name' => Permission::MANAGE_ROLES,
                'display_name' => 'Manage Roles',
                'description' => 'Create, edit, delete roles',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'roles',
                'action' => Permission::ACTION_MANAGE
            ],
            [
                'name' => 'view_roles',
                'display_name' => 'View Roles',
                'description' => 'View roles list',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'roles',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => 'create_roles',
                'display_name' => 'Create Roles',
                'description' => 'Create new roles',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'roles',
                'action' => Permission::ACTION_CREATE
            ],
            [
                'name' => 'edit_roles',
                'display_name' => 'Edit Roles',
                'description' => 'Edit existing roles',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'roles',
                'action' => Permission::ACTION_EDIT
            ],
            [
                'name' => 'delete_roles',
                'display_name' => 'Delete Roles',
                'description' => 'Delete roles',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'roles',
                'action' => Permission::ACTION_DELETE
            ],
            [
                'name' => 'assign_roles',
                'display_name' => 'Assign Roles',
                'description' => 'Assign roles to users',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'roles',
                'action' => 'assign'
            ],

            // Permission Management
            [
                'name' => Permission::MANAGE_PERMISSIONS,
                'display_name' => 'Manage Permissions',
                'description' => 'Create, edit, delete permissions',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'permissions',
                'action' => Permission::ACTION_MANAGE
            ],
            [
                'name' => 'view_permissions',
                'display_name' => 'View Permissions',
                'description' => 'View permissions list',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'permissions',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => 'create_permissions',
                'display_name' => 'Create Permissions',
                'description' => 'Create new permissions',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'permissions',
                'action' => Permission::ACTION_CREATE
            ],
            [
                'name' => 'edit_permissions',
                'display_name' => 'Edit Permissions',
                'description' => 'Edit existing permissions',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'permissions',
                'action' => Permission::ACTION_EDIT
            ],
            [
                'name' => 'assign_permissions',
                'display_name' => 'Assign Permissions',
                'description' => 'Assign permissions to roles',
                'category' => Permission::CATEGORY_ADMIN,
                'resource' => 'permissions',
                'action' => 'assign'
            ],

            // Driver Management
            [
                'name' => Permission::VIEW_DRIVERS,
                'display_name' => 'View Drivers',
                'description' => 'View drivers list and details',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => Permission::CREATE_DRIVERS,
                'display_name' => 'Create Drivers',
                'description' => 'Create new driver profiles',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => Permission::ACTION_CREATE
            ],
            [
                'name' => Permission::EDIT_DRIVERS,
                'display_name' => 'Edit Drivers',
                'description' => 'Edit driver profiles and information',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => Permission::ACTION_EDIT
            ],
            [
                'name' => Permission::DELETE_DRIVERS,
                'display_name' => 'Delete Drivers',
                'description' => 'Delete driver profiles',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => Permission::ACTION_DELETE
            ],
            [
                'name' => Permission::APPROVE_DRIVERS,
                'display_name' => 'Approve Drivers',
                'description' => 'Approve or reject driver applications',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => Permission::ACTION_APPROVE
            ],
            [
                'name' => Permission::VERIFY_DRIVERS,
                'display_name' => 'Verify Drivers',
                'description' => 'Verify driver documents and credentials',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => 'verify'
            ],
            [
                'name' => 'export_drivers',
                'display_name' => 'Export Drivers',
                'description' => 'Export driver data to various formats',
                'category' => Permission::CATEGORY_DRIVER,
                'resource' => 'drivers',
                'action' => 'export'
            ],

            // Company Management
            [
                'name' => Permission::VIEW_COMPANIES,
                'display_name' => 'View Companies',
                'description' => 'View company list and details',
                'category' => Permission::CATEGORY_COMPANY,
                'resource' => 'companies',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => Permission::CREATE_COMPANIES,
                'display_name' => 'Create Companies',
                'description' => 'Create new company profiles',
                'category' => Permission::CATEGORY_COMPANY,
                'resource' => 'companies',
                'action' => Permission::ACTION_CREATE
            ],
            [
                'name' => Permission::EDIT_COMPANIES,
                'display_name' => 'Edit Companies',
                'description' => 'Edit company profiles and information',
                'category' => Permission::CATEGORY_COMPANY,
                'resource' => 'companies',
                'action' => Permission::ACTION_EDIT
            ],
            [
                'name' => Permission::DELETE_COMPANIES,
                'display_name' => 'Delete Companies',
                'description' => 'Delete company profiles',
                'category' => Permission::CATEGORY_COMPANY,
                'resource' => 'companies',
                'action' => Permission::ACTION_DELETE
            ],
            [
                'name' => Permission::APPROVE_COMPANIES,
                'display_name' => 'Approve Companies',
                'description' => 'Approve or reject company applications',
                'category' => Permission::CATEGORY_COMPANY,
                'resource' => 'companies',
                'action' => Permission::ACTION_APPROVE
            ],

            // Report Management
            [
                'name' => Permission::VIEW_REPORTS,
                'display_name' => 'View Reports',
                'description' => 'Access reporting dashboard and analytics',
                'category' => Permission::CATEGORY_REPORT,
                'resource' => 'reports',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => Permission::CREATE_REPORTS,
                'display_name' => 'Create Reports',
                'description' => 'Create custom reports and analytics',
                'category' => Permission::CATEGORY_REPORT,
                'resource' => 'reports',
                'action' => Permission::ACTION_CREATE
            ],
            [
                'name' => Permission::EXPORT_REPORTS,
                'display_name' => 'Export Reports',
                'description' => 'Export reports to various formats',
                'category' => Permission::CATEGORY_REPORT,
                'resource' => 'reports',
                'action' => 'export'
            ],

            // Additional specific permissions
            [
                'name' => 'manage_matching',
                'display_name' => 'Manage Matching',
                'description' => 'Manage driver-company matching system',
                'category' => Permission::CATEGORY_SYSTEM,
                'resource' => 'matching',
                'action' => Permission::ACTION_MANAGE
            ],
            [
                'name' => 'view_audit_logs',
                'display_name' => 'View Audit Logs',
                'description' => 'View system audit logs and activities',
                'category' => Permission::CATEGORY_SYSTEM,
                'resource' => 'audit_logs',
                'action' => Permission::ACTION_VIEW
            ],
            [
                'name' => 'manage_notifications',
                'display_name' => 'Manage Notifications',
                'description' => 'Send and manage system notifications',
                'category' => Permission::CATEGORY_SYSTEM,
                'resource' => 'notifications',
                'action' => Permission::ACTION_MANAGE
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Default permissions created successfully.');
    }
}