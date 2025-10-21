<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debugging Permission System ===\n\n";

// Test SuperAdmin user
$user = App\Models\AdminUser::where('email', 'admin@drivelink.com')->first();
if ($user) {
    echo "SuperAdmin User: {$user->name} ({$user->email})\n";
    echo "Role field: " . ($user->role ?? 'null') . "\n";

    // Check permissions
    $roleSync = app(App\Services\RoleSyncService::class);
    $permissions = $roleSync->getUserPermissions($user);
    echo "Permissions from RoleSyncService: " . count($permissions) . "\n";
    if (count($permissions) > 0) {
        print_r(array_slice($permissions, 0, 5)); // Show first 5
    }

    // Test PermissionHelper
    Auth::guard('admin')->login($user);
    $helper = new App\Helpers\PermissionHelper();

    echo "\nPermissionHelper tests:\n";
    echo "hasPermission('view_dashboard'): " . ($helper->hasPermission('view_dashboard') ? 'YES' : 'NO') . "\n";
    echo "hasRole('super_admin'): " . ($helper->hasRole('super_admin') ? 'YES' : 'NO') . "\n";
    echo "isSuperAdmin(): " . ($helper->isSuperAdmin() ? 'YES' : 'NO') . "\n";

    Auth::guard('admin')->logout();
} else {
    echo "SuperAdmin user not found\n";
}

echo "\n=== Debug Complete ===\n";
