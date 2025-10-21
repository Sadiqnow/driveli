<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug User Access ===\n\n";

// Test Admin user
$adminUser = App\Models\AdminUser::where('email', 'john@drivelink.com')->first();
if ($adminUser) {
    Auth::guard('admin')->login($adminUser);
    $debug = App\Helpers\PermissionHelper::debugUserAccess();

    echo "User ID: {$debug['user_id']}\n";
    echo "Email: {$debug['user_email']}\n";
    echo "Role Field: {$debug['user_role_field']}\n";
    echo "Permissions Count: {$debug['permissions_count']}\n";
    echo "Permissions: " . implode(', ', $debug['permissions_from_service']) . "\n";
    echo "Has Dashboard Permission: " . ($debug['has_dashboard_permission'] ? 'YES' : 'NO') . "\n";
    echo "Has Admin Role: " . ($debug['has_admin_role'] ? 'YES' : 'NO') . "\n";
    echo "Is Super Admin: " . ($debug['is_super_admin'] ? 'YES' : 'NO') . "\n";

    Auth::guard('admin')->logout();
} else {
    echo "Admin user not found\n";
}

echo "\n=== Debug Complete ===\n";
