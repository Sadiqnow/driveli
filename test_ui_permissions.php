<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\AdminUser;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing UI Permission Directives\n";
echo "================================\n\n";

// Test @can directive simulation
function simulateCanDirective($user, $permission) {
    return $user->hasPermission($permission) ? 'SHOW' : 'HIDE';
}

$users = [
    'Super Admin' => AdminUser::where('role', 'Super Admin')->first(),
    'Admin' => AdminUser::where('role', 'Admin')->first(),
];

$uiElements = [
    'dashboard_link' => 'view_dashboard',
    'drivers_menu' => 'manage_drivers',
    'companies_menu' => 'manage_companies',
    'matching_menu' => 'manage_matching',
    'verification_menu' => 'manage_verification',
    'requests_menu' => 'manage_requests',
    'commissions_menu' => 'manage_commissions',
    'reports_menu' => 'view_reports',
    'users_menu' => 'manage_users',
    'approve_button' => 'approve_driver',
    'reject_button' => 'reject_driver',
    'delete_button' => 'delete_driver',
];

echo "UI Element Visibility by Role:\n";
echo "==============================\n\n";

foreach ($users as $roleName => $user) {
    if ($user) {
        echo "{$roleName} User Interface:\n";
        echo str_repeat('-', 40) . "\n";

        foreach ($uiElements as $element => $permission) {
            $visibility = simulateCanDirective($user, $permission);
            $icon = $visibility === 'SHOW' ? '👁️' : '🙈';
            echo "{$icon} {$element}: {$visibility}\n";
        }
        echo "\n";
    }
}

// Test menu structure simulation
echo "Menu Structure Test:\n";
echo "===================\n\n";

$menuStructure = [
    'Dashboard' => 'view_dashboard',
    'Driver Management' => [
        'View Drivers' => 'manage_drivers',
        'Add Driver' => 'create_driver',
        'Edit Driver' => 'edit_driver',
        'Delete Driver' => 'delete_driver',
    ],
    'Company Management' => [
        'View Companies' => 'manage_companies',
        'Add Company' => 'create_company',
        'Edit Company' => 'edit_company',
    ],
    'Matching System' => 'manage_matching',
    'Verification' => 'manage_verification',
    'Requests' => 'manage_requests',
    'Commissions' => 'manage_commissions',
    'Reports' => 'view_reports',
    'User Management' => 'manage_users',
];

foreach ($users as $roleName => $user) {
    if ($user) {
        echo "{$roleName} Menu Structure:\n";
        echo str_repeat('=', 30) . "\n";

        foreach ($menuStructure as $menuItem => $permissions) {
            if (is_array($permissions)) {
                // Submenu
                $visibleItems = [];
                foreach ($permissions as $subItem => $perm) {
                    if (simulateCanDirective($user, $perm) === 'SHOW') {
                        $visibleItems[] = $subItem;
                    }
                }

                if (!empty($visibleItems)) {
                    echo "📁 {$menuItem}\n";
                    foreach ($visibleItems as $item) {
                        echo "  ├── {$item}\n";
                    }
                } else {
                    echo "🚫 {$menuItem} (hidden)\n";
                }
            } else {
                // Single menu item
                $visibility = simulateCanDirective($user, $permissions);
                $icon = $visibility === 'SHOW' ? '📄' : '🚫';
                echo "{$icon} {$menuItem}\n";
            }
        }
        echo "\n";
    }
}

echo "UI Test Complete\n";
