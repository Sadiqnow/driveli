<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Admin User Menu Filtering ===\n\n";

// Test Admin user
$adminUser = App\Models\AdminUser::where('email', 'john@drivelink.com')->first();
if ($adminUser) {
    Auth::guard('admin')->login($adminUser);
    $helper = new App\Helpers\PermissionHelper();

    echo "Testing menu filtering for Admin ({$adminUser->name}):\n";
    $filteredMenus = $helper->getFilteredMenus();

    echo "Filtered menus count: " . count($filteredMenus) . "\n";
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
    echo "Admin user not found\n";
}

echo "\n=== Test Complete ===\n";
