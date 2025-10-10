<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AdminUser;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => Role::SUPER_ADMIN,
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'level' => Role::LEVEL_SUPER_ADMIN,
                'permissions' => 'all' // Special case - gets all permissions
            ],
            [
                'name' => Role::ADMIN,
                'display_name' => 'Administrator',
                'description' => 'High-level admin access for managing users and system',
                'level' => Role::LEVEL_ADMIN,
                'permissions' => [
                    Permission::VIEW_DASHBOARD,
                    Permission::MANAGE_USERS,
                    Permission::VIEW_DRIVERS,
                    Permission::CREATE_DRIVERS,
                    Permission::EDIT_DRIVERS,
                    Permission::APPROVE_DRIVERS,
                    Permission::VERIFY_DRIVERS,
                    Permission::VIEW_COMPANIES,
                    Permission::CREATE_COMPANIES,
                    Permission::EDIT_COMPANIES,
                    Permission::APPROVE_COMPANIES,
                    Permission::VIEW_REPORTS,
                    Permission::CREATE_REPORTS,
                    Permission::EXPORT_REPORTS,
                    'manage_matching',
                    'manage_notifications'
                ]
            ],
            [
                'name' => Role::MODERATOR,
                'display_name' => 'Moderator',
                'description' => 'Moderate users and content with limited admin access',
                'level' => Role::LEVEL_MODERATOR,
                'permissions' => [
                    Permission::VIEW_DASHBOARD,
                    'view_users',
                    Permission::VIEW_DRIVERS,
                    Permission::EDIT_DRIVERS,
                    Permission::VERIFY_DRIVERS,
                    Permission::VIEW_COMPANIES,
                    Permission::EDIT_COMPANIES,
                    Permission::VIEW_REPORTS,
                    'manage_notifications'
                ]
            ],
            [
                'name' => Role::VIEWER,
                'display_name' => 'Viewer',
                'description' => 'Read-only access to system data',
                'level' => Role::LEVEL_USER,
                'permissions' => [
                    Permission::VIEW_DASHBOARD,
                    'view_users',
                    Permission::VIEW_DRIVERS,
                    Permission::VIEW_COMPANIES,
                    Permission::VIEW_REPORTS
                ]
            ],
            [
                'name' => 'driver_manager',
                'display_name' => 'Driver Manager',
                'description' => 'Specialized role for managing driver operations',
                'level' => Role::LEVEL_MODERATOR,
                'permissions' => [
                    Permission::VIEW_DASHBOARD,
                    Permission::VIEW_DRIVERS,
                    Permission::CREATE_DRIVERS,
                    Permission::EDIT_DRIVERS,
                    Permission::APPROVE_DRIVERS,
                    Permission::VERIFY_DRIVERS,
                    'export_drivers',
                    Permission::VIEW_REPORTS,
                    'manage_matching'
                ]
            ],
            [
                'name' => 'company_manager',
                'display_name' => 'Company Manager',
                'description' => 'Specialized role for managing company operations',
                'level' => Role::LEVEL_MODERATOR,
                'permissions' => [
                    Permission::VIEW_DASHBOARD,
                    Permission::VIEW_COMPANIES,
                    Permission::CREATE_COMPANIES,
                    Permission::EDIT_COMPANIES,
                    Permission::APPROVE_COMPANIES,
                    Permission::VIEW_DRIVERS,
                    Permission::VIEW_REPORTS,
                    'manage_matching'
                ]
            ],
            [
                'name' => 'auditor',
                'display_name' => 'Auditor',
                'description' => 'Special access to audit logs and system monitoring',
                'level' => Role::LEVEL_MODERATOR,
                'permissions' => [
                    Permission::VIEW_DASHBOARD,
                    Permission::VIEW_REPORTS,
                    Permission::CREATE_REPORTS,
                    Permission::EXPORT_REPORTS,
                    'view_audit_logs',
                    'view_users',
                    Permission::VIEW_DRIVERS,
                    Permission::VIEW_COMPANIES
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Assign permissions to role
            if ($permissions === 'all') {
                // Super admin gets all permissions
                $allPermissions = Permission::where('is_active', true)->get();
                foreach ($allPermissions as $permission) {
                    $role->givePermission($permission);
                }
            } else {
                // Assign specific permissions
                foreach ($permissions as $permissionName) {
                    $permission = Permission::where('name', $permissionName)->first();
                    if ($permission) {
                        $role->givePermission($permission);
                    }
                }
            }
        }

        // Assign Super Admin role to existing admin users with 'Super Admin' role
        $superAdmins = AdminUser::where('role', 'Super Admin')->get();
        $superAdminRole = Role::where('name', Role::SUPER_ADMIN)->first();
        
        foreach ($superAdmins as $admin) {
            if ($superAdminRole && !$admin->hasRole(Role::SUPER_ADMIN)) {
                $admin->assignRole($superAdminRole);
            }
        }

        // Assign Admin role to other admin users
        $otherAdmins = AdminUser::where('role', '!=', 'Super Admin')
                               ->whereNotNull('role')
                               ->get();
        $adminRole = Role::where('name', Role::ADMIN)->first();
        
        foreach ($otherAdmins as $admin) {
            if ($adminRole && !$admin->activeRoles()->exists()) {
                $admin->assignRole($adminRole);
            }
        }

        $this->command->info('Default roles created and assigned successfully.');
    }
}