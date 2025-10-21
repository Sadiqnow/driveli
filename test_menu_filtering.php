<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Menu Filtering with Mock Auth ===\n\n";

// Mock authentication for SuperAdmin
$user = App\Models\AdminUser::where('email', 'admin@drivelink.com')->first();
if ($user) {
    echo "Testing as SuperAdmin: {$user->name}\n";

    // Mock auth guard
    Auth::guard('admin')->login($user);

    $helper = new App\Helpers\PermissionHelper();
    $filteredMenus = $helper->getFilteredMenus();

    echo "Filtered menus for SuperAdmin: " . count($filteredMenus) . "\n";
    foreach ($filteredMenus as $key => $menu) {
        echo "  - {$key}: " . ($menu['label'] ?? 'No label') . "\n";
        if (isset($menu['submenu'])) {
            foreach ($menu['submenu'] as $subKey => $subMenu) {
                echo "    - {$subKey}: " . ($subMenu['label'] ?? 'No label') . "\n";
            }
        }
    }

    Auth::guard('admin')->logout();
} else {
    echo "SuperAdmin user not found\n";
}

// Test with Admin user
$adminUser = App\Models\AdminUser::where('email', 'john@drivelink.com')->first();
if ($adminUser) {
    echo "\nTesting as Admin: {$adminUser->name}\n";

    Auth::guard('admin')->login($adminUser);

    $helper = new App\Helpers\PermissionHelper();
    $filteredMenus = $helper->getFilteredMenus();

    echo "Filtered menus for Admin: " . count($filteredMenus) . "\n";
    foreach ($filteredMenus as $key => $menu) {
        echo "  - {$key}: " . ($menu['label'] ?? 'No label') . "\n";
    }

    Auth::guard('admin')->logout();
} else {
    echo "Admin user not found\n";
}

echo "\n=== Menu Filtering Test Complete ===\n";
