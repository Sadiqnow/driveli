<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Menu Filtering with Fixed Logic ===\n\n";

// Test SuperAdmin user
$user = App\Models\AdminUser::where('email', 'admin@drivelink.com')->first();
if ($user) {
    Auth::guard('admin')->login($user);
    $helper = new App\Helpers\PermissionHelper();

    echo "Testing menu filtering for SuperAdmin:\n";
    $filteredMenus = $helper->getFilteredMenus();

    echo "Filtered menus count: " . count($filteredMenus) . "\n";
    foreach ($filteredMenus as $key => $menu) {
        echo "  - {$key}: " . ($menu['label'] ?? 'No label') . "\n";
    }

    Auth::guard('admin')->logout();
}

echo "\n=== Test Complete ===\n";
