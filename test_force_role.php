<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Force Role Setting ===\n\n";

// Test Admin user
$adminUser = App\Models\AdminUser::where('email', 'john@drivelink.com')->first();
if ($adminUser) {
    Auth::guard('admin')->login($adminUser);

    echo "Before force set:\n";
    $debug = App\Helpers\PermissionHelper::debugUserAccess();
    echo "Role: {$debug['user_role_field']}\n";
    echo "Has Admin role: " . ($debug['has_admin_role'] ? 'YES' : 'NO') . "\n";
    echo "Has Dashboard: " . ($debug['has_dashboard_permission'] ? 'YES' : 'NO') . "\n\n";

    // Force set role to Admin
    App\Helpers\PermissionHelper::forceSetRole('Admin');

    echo "After force set to 'Admin':\n";
    $debug = App\Helpers\PermissionHelper::debugUserAccess();
    echo "Role: {$debug['user_role_field']}\n";
    echo "Has Admin role: " . ($debug['has_admin_role'] ? 'YES' : 'NO') . "\n";
    echo "Has Dashboard: " . ($debug['has_dashboard_permission'] ? 'YES' : 'NO') . "\n\n";

    // Test menu filtering
    $filteredMenus = App\Helpers\PermissionHelper::getFilteredMenus();
    echo "Filtered menus count: " . count($filteredMenus) . "\n";
    foreach ($filteredMenus as $key => $menu) {
        echo "  - {$key}: " . ($menu['label'] ?? 'No label') . "\n";
    }

    Auth::guard('admin')->logout();
} else {
    echo "Admin user not found\n";
}

echo "\n=== Test Complete ===\n";
