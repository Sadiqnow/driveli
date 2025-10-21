<?php

return [
    'dashboard' => [
        'permission' => 'view_dashboard',
        'label' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'admin.dashboard',
        'roles' => ['super_admin', 'admin', 'moderator', 'agent', 'driver']
    ],
    'users' => [
        'permission' => 'manage_users',
        'label' => 'Users',
        'icon' => 'fas fa-users',
        'submenu' => [
            'all_users' => [
                'permission' => 'view_users',
                'label' => 'All Users',
                'route' => 'admin.users.index',
                'roles' => ['super_admin', 'admin']
            ],
            'add_user' => [
                'permission' => 'create_users',
                'label' => 'Add User',
                'route' => 'admin.users.create',
                'roles' => ['super_admin', 'admin']
            ]
        ],
        'roles' => ['super_admin', 'admin']
    ],
    'roles_permissions' => [
        'permission' => 'view_roles',
        'label' => 'Roles & Permissions',
        'icon' => 'fas fa-shield-alt',
        'submenu' => [
            'roles' => [
                'permission' => 'manage_roles',
                'label' => 'Roles',
                'route' => 'admin.roles.index',
                'roles' => ['super_admin']
            ],
            'permissions' => [
                'permission' => 'manage_permissions',
                'label' => 'Permissions',
                'route' => 'admin.permissions.index',
                'roles' => ['super_admin']
            ]
        ],
        'roles' => ['super_admin']
    ],
    'drivers' => [
        'permission' => 'view_drivers',
        'label' => 'Drivers',
        'icon' => 'fas fa-car',
        'submenu' => [
            'all_drivers' => [
                'permission' => 'view_drivers',
                'label' => 'All Drivers',
                'route' => 'admin.superadmin.drivers.index',
                'roles' => ['super_admin', 'admin', 'moderator']
            ],
            'add_driver' => [
                'permission' => 'create_drivers',
                'label' => 'Add Driver',
                'route' => 'admin.superadmin.drivers.create',
                'roles' => ['super_admin', 'admin']
            ],
            'verification' => [
                'permission' => 'verify_documents',
                'label' => 'Verification',
                'route' => 'admin.verification.dashboard',
                'roles' => ['super_admin', 'admin', 'moderator']
            ]
        ],
        'roles' => ['super_admin', 'admin', 'moderator', 'agent']
    ],
    'companies' => [
        'permission' => 'view_companies',
        'label' => 'Companies',
        'icon' => 'fas fa-building',
        'submenu' => [
            'all_companies' => [
                'permission' => 'view_companies',
                'label' => 'All Companies',
                'route' => 'admin.companies.index',
                'roles' => ['super_admin', 'admin']
            ],
            'requests' => [
                'permission' => 'manage_company_requests',
                'label' => 'Requests',
                'route' => 'admin.requests.index',
                'roles' => ['super_admin', 'admin']
            ]
        ],
        'roles' => ['super_admin', 'admin']
    ],
    'matching' => [
        'permission' => 'manage_matching',
        'label' => 'Matching',
        'icon' => 'fas fa-handshake',
        'submenu' => [
            'dashboard' => [
                'permission' => 'view_matching_dashboard',
                'label' => 'Dashboard',
                'route' => 'admin.matching.dashboard',
                'roles' => ['super_admin', 'admin']
            ],
            'manual_matching' => [
                'permission' => 'manage_matching',
                'label' => 'Manual Matching',
                'route' => 'admin.matching.index',
                'roles' => ['super_admin', 'admin']
            ]
        ],
        'roles' => ['super_admin', 'admin']
    ],
    'reports' => [
        'permission' => 'view_reports',
        'label' => 'Reports',
        'icon' => 'fas fa-chart-bar',
        'submenu' => [
            'overview' => [
                'permission' => 'view_reports',
                'label' => 'Overview',
                'route' => 'admin.reports.index',
                'roles' => ['super_admin', 'admin']
            ],
            'analytics' => [
                'permission' => 'view_analytics',
                'label' => 'Analytics',
                'route' => 'admin.reports.dashboard',
                'roles' => ['super_admin', 'admin']
            ]
        ],
        'roles' => ['super_admin', 'admin']
    ],
    'super_admin' => [
        'permission' => 'super_admin_access',
        'label' => 'Super Admin',
        'icon' => 'fas fa-crown',
        'submenu' => [
            'dashboard' => [
                'permission' => 'super_admin_access',
                'label' => 'Dashboard',
                'route' => 'admin.superadmin.index',
                'roles' => ['super_admin']
            ],
            'audit_logs' => [
                'permission' => 'view_audit_logs',
                'label' => 'Audit Logs',
                'route' => 'admin.superadmin.audit-logs',
                'roles' => ['super_admin']
            ]
        ],
        'roles' => ['super_admin']
    ],
    'settings' => [
        'permission' => 'manage_settings',
        'label' => 'Settings',
        'icon' => 'fas fa-cog',
        'route' => 'admin.superadmin.settings',
        'roles' => ['super_admin', 'admin']
    ]
];
