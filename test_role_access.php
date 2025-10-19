<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\AdminUser;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing Role-Based Access Control\n";
echo "=================================\n\n";

// Create test users for different roles
$roles = ['Super Admin', 'Admin'];

foreach ($roles as $role) {
    $user = AdminUser::where('role', $role)->first();
    if (!$user) {
        // Create test user if doesn't exist
        $user = AdminUser::create([
            'name' => ucfirst($role) . ' Test User',
            'email' => strtolower(str_replace(' ', '_', $role)) . '@test.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'status' => 'Active',
            'is_active' => true,
        ]);
        echo "Created test user: {$user->name} ({$role})\n";
    } else {
        echo "Found existing user: {$user->name} ({$role})\n";
    }
}

echo "\nTesting Permission Access by Role:\n";
echo "==================================\n";

$permissions = [
    'view_dashboard' => 'Dashboard Access',
    'manage_drivers' => 'Driver Management',
    'manage_companies' => 'Company Management',
    'manage_matching' => 'Matching System',
    'manage_verification' => 'Verification System',
    'manage_requests' => 'Request Management',
    'manage_commissions' => 'Commission Management',
    'view_reports' => 'Reports Access',
    'manage_users' => 'User Management',
];

foreach ($roles as $role) {
    $user = AdminUser::where('role', $role)->first();
    if ($user) {
        echo "\n{$role} Permissions:\n";
        echo str_repeat('-', 30) . "\n";

        foreach ($permissions as $perm => $desc) {
            $hasPermission = $user->hasPermission($perm);
            echo "{$desc}: " . ($hasPermission ? '✅' : '❌') . "\n";
        }
    }
}

echo "\nTesting Route Access Simulation:\n";
echo "================================\n";

// Simulate middleware checks
$testRoutes = [
    'admin.drivers.index' => ['Admin', 'manage_drivers'],
    'admin.companies.index' => ['Admin', 'manage_companies'],
    'admin.matching.index' => ['Admin', 'manage_matching'],
    'admin.verification.dashboard' => ['Admin', 'manage_verification'],
    'admin.requests.index' => ['Admin', 'manage_requests'],
    'admin.commissions.index' => ['Admin', 'manage_commissions'],
    'admin.reports.index' => ['Admin', 'view_reports'],
    'admin.users.index' => ['Super Admin', 'manage_users'],
];

foreach ($testRoutes as $route => $requirements) {
    list($requiredRole, $requiredPermission) = $requirements;

    echo "\nRoute: {$route}\n";
    echo "Required: {$requiredRole} + {$requiredPermission}\n";

    foreach ($roles as $userRole) {
        $user = AdminUser::where('role', $userRole)->first();
        if ($user) {
            $hasRole = $user->hasRole($requiredRole) || $user->hasRole('Super Admin');
            $hasPermission = $user->hasPermission($requiredPermission);

            $access = ($hasRole && $hasPermission) ? '✅ ALLOWED' : '❌ DENIED';
            echo "  {$userRole}: {$access}\n";
        }
    }
}

echo "\nTest Complete\n";
