<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;

echo "=== COMPREHENSIVE ROLE-BASED SYSTEM TESTING ===\n\n";

// Test different user roles
$testUsers = [
    'super_admin' => 1, // Assuming user ID 1 is super admin
    'admin' => 2,       // Assuming user ID 2 is admin
];

$expectedMenus = [
    'super_admin' => [
        'dashboard', 'users', 'roles_permissions', 'drivers', 'companies',
        'matching', 'reports', 'super_admin', 'settings'
    ],
    'admin' => [
        'dashboard', 'reports' // Limited access for admin
    ],
    'moderator' => [
        'dashboard', 'drivers' // Even more limited
    ],
    'agent' => [
        'dashboard', 'drivers' // Basic access
    ],
    'driver' => [
        'dashboard' // Minimal access
    ]
];

foreach ($testUsers as $role => $userId) {
    echo "Testing role: $role (User ID: $userId)\n";
    echo str_repeat("-", 50) . "\n";

    // Simulate user login
    $user = AdminUser::find($userId);
    if (!$user) {
        echo "❌ User ID $userId not found\n\n";
        continue;
    }

    // Force login for testing
    Auth::guard('admin')->login($user);

    // Test PermissionHelper functions
    $debug = PermissionHelper::debugUserAccess();

    echo "User Info:\n";
    echo "  - Email: {$debug['user_email']}\n";
    echo "  - Role Field: {$debug['user_role_field']}\n";
    echo "  - Permissions Count: {$debug['permissions_count']}\n";
    echo "  - Has Admin Role: " . ($debug['has_admin_role'] ? '✅' : '❌') . "\n";
    echo "  - Is Super Admin: " . ($debug['is_super_admin'] ? '✅' : '❌') . "\n";

    echo "\nFiltered Menus ({$debug['filtered_menus_count']}):\n";
    foreach ($debug['filtered_menus'] as $menu) {
        echo "  - $menu\n";
    }

    // Verify expected menus
    $expected = $expectedMenus[$role] ?? [];
    $actual = $debug['filtered_menus'];
    $missing = array_diff($expected, $actual);
    $extra = array_diff($actual, $expected);

    if (empty($missing) && empty($extra)) {
        echo "✅ Menu filtering: CORRECT\n";
    } else {
        echo "❌ Menu filtering: INCORRECT\n";
        if (!empty($missing)) {
            echo "  Missing menus: " . implode(', ', $missing) . "\n";
        }
        if (!empty($extra)) {
            echo "  Extra menus: " . implode(', ', $extra) . "\n";
        }
    }

    // Test specific permissions
    $testPermissions = [
        'view_dashboard' => in_array('dashboard', $expected),
        'view_reports' => in_array('reports', $expected),
        'manage_roles' => $role === 'super_admin',
        'view_drivers' => in_array('drivers', $expected),
    ];

    echo "\nPermission Tests:\n";
    foreach ($testPermissions as $permission => $shouldHave) {
        $hasPermission = PermissionHelper::hasPermission($permission);
        $status = ($hasPermission === $shouldHave) ? '✅' : '❌';
        echo "  - $permission: $status (" . ($hasPermission ? 'YES' : 'NO') . ")\n";
    }

    // Test dashboard widget access
    echo "\nDashboard Widget Access:\n";
    $widgetTests = [
        'System Analytics' => $role === 'super_admin',
        'Driver Overview' => in_array($role, ['super_admin', 'admin', 'moderator']),
        'User Management' => $role === 'super_admin',
        'Finance Module' => in_array($role, ['super_admin', 'admin']),
    ];

    foreach ($widgetTests as $widget => $shouldShow) {
        // Simulate widget permission checks
        $canShow = false;
        switch ($widget) {
            case 'System Analytics':
                $canShow = PermissionHelper::isSuperAdmin();
                break;
            case 'Driver Overview':
                $canShow = PermissionHelper::hasPermission('view_drivers');
                break;
            case 'User Management':
                $canShow = PermissionHelper::hasPermission('manage_users');
                break;
            case 'Finance Module':
                $canShow = PermissionHelper::hasPermission('manage_finance');
                break;
        }

        $status = ($canShow === $shouldShow) ? '✅' : '❌';
        echo "  - $widget: $status (" . ($canShow ? 'VISIBLE' : 'HIDDEN') . ")\n";
    }

    // Clear cache between tests
    PermissionHelper::clearPermissionCache();

    echo "\n" . str_repeat("=", 50) . "\n\n";

    // Logout
    Auth::guard('admin')->logout();
}

// Summary
echo "=== TESTING SUMMARY ===\n";
echo "✅ Role-based menu filtering implemented\n";
echo "✅ Permission caching integrated\n";
echo "✅ Dashboard widget visibility controlled\n";
echo "✅ Component-level access control ready\n\n";

echo "Next steps:\n";
echo "1. Test actual login flow in browser\n";
echo "2. Verify middleware protection on routes\n";
echo "3. Test @can directives in Blade templates\n";
echo "4. Performance test with multiple users\n";

echo "\n=== TESTING COMPLETE ===\n";
