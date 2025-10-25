<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AdminUser;
use Illuminate\Http\Request;
use App\Http\Middleware\RolePermissionMiddleware;
use App\Http\Middleware\SuperAdminDriverAccess;

echo "=== TESTING SUPER ADMIN ACCESS ===\n\n";

// Find the Super Admin user
$user = AdminUser::where('email', 'admin@drivelink.com')->first();
if (!$user) {
    echo "ERROR: Super Admin user not found!\n";
    exit(1);
}

echo "Testing user: {$user->name} ({$user->email})\n";
echo 'Is Super Admin: ' . ($user->isSuperAdmin() ? 'YES' : 'NO') . PHP_EOL;
echo 'Has manage_superadmin permission: ' . ($user->hasPermission('manage_superadmin') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has manage_drivers permission: ' . ($user->hasPermission('manage_drivers') ? 'YES' : 'NO') . PHP_EOL;

// Test RolePermissionMiddleware
echo "\n=== TESTING RolePermissionMiddleware ===\n";
$roleMiddleware = new RolePermissionMiddleware();
$request = Request::create('/admin/superadmin/drivers', 'GET');

// Mock authentication
auth('admin')->login($user);

try {
    $response = $roleMiddleware->handle($request, function($req) {
        return response('OK');
    }, 'Super Admin', 'manage_superadmin');

    if ($response->getStatusCode() === 200) {
        echo "✓ RolePermissionMiddleware: PASSED\n";
    } else {
        echo "✗ RolePermissionMiddleware: FAILED (Status: {$response->getStatusCode()})\n";
        echo "Response: " . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "✗ RolePermissionMiddleware: ERROR - " . $e->getMessage() . "\n";
}

// Test SuperAdminDriverAccess middleware
echo "\n=== TESTING SuperAdminDriverAccess ===\n";
$driverMiddleware = new SuperAdminDriverAccess();

try {
    $response = $driverMiddleware->handle($request, function($req) {
        return response('OK');
    });

    if ($response->getStatusCode() === 200) {
        echo "✓ SuperAdminDriverAccess: PASSED\n";
    } else {
        echo "✗ SuperAdminDriverAccess: FAILED (Status: {$response->getStatusCode()})\n";
        echo "Response: " . $response->getContent() . "\n";
    }
} catch (Exception $e) {
    echo "✗ SuperAdminDriverAccess: ERROR - " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
