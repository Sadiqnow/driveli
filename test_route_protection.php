<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use App\Models\AdminUser;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing Route Protection & Access Control\n";
echo "=========================================\n\n";

// Test routes that should be protected
$protectedRoutes = [
    'admin.drivers.index' => ['Admin', 'manage_drivers'],
    'admin.companies.index' => ['Admin', 'manage_companies'],
    'admin.matching.index' => ['Admin', 'manage_matching'],
    'admin.verification.dashboard' => ['Admin', 'manage_verification'],
    'admin.requests.index' => ['Admin', 'manage_requests'],
    'admin.commissions.index' => ['Admin', 'manage_commissions'],
    'admin.reports.index' => ['Admin', 'view_reports'],
    'admin.users.index' => ['Super Admin', 'manage_users'],
];

// Test with different user roles
$testUsers = [
    'Super Admin' => AdminUser::where('role', 'Super Admin')->first(),
    'Admin' => AdminUser::where('role', 'Admin')->first(),
];

echo "Route Access Test Results:\n";
echo "==========================\n\n";

foreach ($testUsers as $userRole => $user) {
    if ($user) {
        echo "{$userRole} User Access:\n";
        echo str_repeat('-', 30) . "\n";

        foreach ($protectedRoutes as $route => $requirements) {
            list($requiredRole, $requiredPermission) = $requirements;

            $hasRole = $user->hasRole($requiredRole) || $user->hasRole('Super Admin');
            $hasPermission = $user->hasPermission($requiredPermission);

            $accessGranted = $hasRole && $hasPermission;
            $icon = $accessGranted ? '✅' : '❌';
            $status = $accessGranted ? 'ALLOWED' : 'DENIED';

            echo "{$icon} {$route}: {$status} ({$requiredRole} + {$requiredPermission})\n";
        }
        echo "\n";
    }
}

// Test middleware enforcement simulation
echo "Middleware Enforcement Test:\n";
echo "============================\n\n";

$request = new Request();
$request->server->set('REQUEST_METHOD', 'GET');
$request->server->set('REMOTE_ADDR', '127.0.0.1');

foreach ($testUsers as $userRole => $user) {
    if ($user) {
        echo "Testing middleware for {$userRole}:\n";

        // Test accessing admin.drivers.index (requires Admin + manage_drivers)
        auth('admin')->login($user);

        try {
            // Simulate route access
            $hasAccess = $user->hasRole('Admin') && $user->hasPermission('manage_drivers');
            $hasAccess = $hasAccess || $user->hasRole('Super Admin'); // Super admin bypass

            $result = $hasAccess ? '✅ ACCESS GRANTED' : '❌ ACCESS DENIED';
            echo "  admin.drivers.index: {$result}\n";

        } catch (\Exception $e) {
            echo "  admin.drivers.index: ❌ ERROR - {$e->getMessage()}\n";
        }

        // Test accessing admin.users.index (requires Super Admin + manage_users)
        try {
            $hasAccess = $user->hasRole('Super Admin') && $user->hasPermission('manage_users');

            $result = $hasAccess ? '✅ ACCESS GRANTED' : '❌ ACCESS DENIED';
            echo "  admin.users.index: {$result}\n";

        } catch (\Exception $e) {
            echo "  admin.users.index: ❌ ERROR - {$e->getMessage()}\n";
        }

        auth('admin')->logout();
        echo "\n";
    }
}

// Test controller method protection
echo "Controller Method Protection Test:\n";
echo "==================================\n\n";

$controllerTests = [
    'AdminRequestController@index' => 'manage_requests',
    'DriverController@index' => 'manage_drivers',
    'VerificationController@dashboard' => 'manage_verification',
    'AdminReportController@index' => 'view_reports',
    'CommissionsController@index' => 'manage_commissions',
];

foreach ($testUsers as $userRole => $user) {
    if ($user) {
        echo "{$userRole} Controller Access:\n";
        echo str_repeat('-', 30) . "\n";

        foreach ($controllerTests as $controller => $permission) {
            $hasPermission = $user->hasPermission($permission);
            $icon = $hasPermission ? '✅' : '❌';
            $status = $hasPermission ? 'ALLOWED' : 'DENIED';

            echo "{$icon} {$controller}: {$status}\n";
        }
        echo "\n";
    }
}

echo "Route Protection Test Complete\n";
