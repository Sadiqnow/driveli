<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debugging Admin User Permissions ===\n\n";

// Test Admin user
$adminUser = App\Models\AdminUser::where('email', 'john@drivelink.com')->first();
if ($adminUser) {
    echo "Admin User: {$adminUser->name} ({$adminUser->email})\n";
    echo "Role field: " . ($adminUser->role ?? 'null') . "\n";

    // Check permissions
    $roleSync = app(App\Services\RoleSyncService::class);
    $permissions = $roleSync->getUserPermissions($adminUser);
    echo "Permissions from RoleSyncService: " . count($permissions) . "\n";

    // Test PermissionHelper
    Auth::guard('admin')->login($adminUser);
    $helper = new App\Helpers\PermissionHelper();

    echo "\nPermissionHelper tests:\n";
    echo "hasPermission('view_dashboard'): " . ($helper->hasPermission('view_dashboard') ? 'YES' : 'NO') . "\n";
    echo "hasRole('admin'): " . ($helper->hasRole('admin') ? 'YES' : 'NO') . "\n";
    echo "hasRole('Admin'): " . ($helper->hasRole('Admin') ? 'YES' : 'NO') . "\n";
    echo "isSuperAdmin(): " . ($helper->isSuperAdmin() ? 'YES' : 'NO') . "\n";

    Auth::guard('admin')->logout();
} else {
    echo "Admin user not found\n";
}

echo "\n=== Debug Complete ===\n";
