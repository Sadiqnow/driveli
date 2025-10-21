<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Menu Filtering Logic ===\n\n";

// Test SuperAdmin user
$user = App\Models\AdminUser::where('email', 'admin@drivelink.com')->first();
if ($user) {
    Auth::guard('admin')->login($user);
    $helper = new App\Helpers\PermissionHelper();

    echo "Testing individual permissions:\n";
    $menus = config('menus');
    foreach ($menus as $key => $menu) {
        $hasPermission = $helper->hasPermission($menu['permission'] ?? '');
        $hasRole = isset($menu['roles']) ? $helper->hasAnyRole($menu['roles']) : true;
        $canAccess = $hasPermission || $hasRole;

        echo "  {$key}: permission='" . ($menu['permission'] ?? 'none') . "' -> " . ($hasPermission ? 'YES' : 'NO') . ", roles=" . (isset($menu['roles']) ? implode(',', $menu['roles']) : 'none') . " -> " . ($hasRole ? 'YES' : 'NO') . ", canAccess=" . ($canAccess ? 'YES' : 'NO') . "\n";
    }

    Auth::guard('admin')->logout();
}

echo "\n=== Test Complete ===\n";
