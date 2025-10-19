<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\AdminUser;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing RBAC Permission System\n";
echo "===============================\n\n";

// Test Super Admin permissions
$user = AdminUser::where('role', 'Super Admin')->first();
if ($user) {
    echo "Super Admin User Found: {$user->name} ({$user->email})\n";
    echo "Super Admin has view_dashboard: " . ($user->hasPermission('view_dashboard') ? 'YES' : 'NO') . "\n";
    echo "Super Admin has manage_drivers: " . ($user->hasPermission('manage_drivers') ? 'YES' : 'NO') . "\n";
    echo "Super Admin has manage_users: " . ($user->hasPermission('manage_users') ? 'YES' : 'NO') . "\n";
    echo "Super Admin has view_reports: " . ($user->hasPermission('view_reports') ? 'YES' : 'NO') . "\n";
} else {
    echo "No Super Admin found\n";
}

echo "\n";

// Test Admin permissions
$adminUser = AdminUser::where('role', 'Admin')->first();
if ($adminUser) {
    echo "Admin User Found: {$adminUser->name} ({$adminUser->email})\n";
    echo "Admin has view_dashboard: " . ($adminUser->hasPermission('view_dashboard') ? 'YES' : 'NO') . "\n";
    echo "Admin has manage_drivers: " . ($adminUser->hasPermission('manage_drivers') ? 'YES' : 'NO') . "\n";
    echo "Admin has manage_users: " . ($adminUser->hasPermission('manage_users') ? 'YES' : 'NO') . "\n";
    echo "Admin has view_reports: " . ($adminUser->hasPermission('view_reports') ? 'YES' : 'NO') . "\n";
} else {
    echo "No Admin found\n";
}

echo "\n";

// Test Moderator permissions
$moderatorUser = AdminUser::where('role', 'Moderator')->first();
if ($moderatorUser) {
    echo "Moderator User Found: {$moderatorUser->name} ({$moderatorUser->email})\n";
    echo "Moderator has view_dashboard: " . ($moderatorUser->hasPermission('view_dashboard') ? 'YES' : 'NO') . "\n";
    echo "Moderator has manage_drivers: " . ($moderatorUser->hasPermission('manage_drivers') ? 'YES' : 'NO') . "\n";
    echo "Moderator has manage_users: " . ($moderatorUser->hasPermission('manage_users') ? 'YES' : 'NO') . "\n";
    echo "Moderator has view_reports: " . ($moderatorUser->hasPermission('view_reports') ? 'YES' : 'NO') . "\n";
} else {
    echo "No Moderator found\n";
}

echo "\n";

// Test Agent permissions
$agentUser = AdminUser::where('role', 'Agent')->first();
if ($agentUser) {
    echo "Agent User Found: {$agentUser->name} ({$agentUser->email})\n";
    echo "Agent has view_dashboard: " . ($agentUser->hasPermission('view_dashboard') ? 'YES' : 'NO') . "\n";
    echo "Agent has manage_drivers: " . ($agentUser->hasPermission('manage_drivers') ? 'YES' : 'NO') . "\n";
    echo "Agent has manage_users: " . ($agentUser->hasPermission('manage_users') ? 'YES' : 'NO') . "\n";
    echo "Agent has view_reports: " . ($agentUser->hasPermission('view_reports') ? 'YES' : 'NO') . "\n";
} else {
    echo "No Agent found\n";
}

echo "\nTesting Role Methods:\n";
echo "====================\n";

if ($user) {
    echo "Super Admin hasRole('Super Admin'): " . ($user->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
    echo "Super Admin hasRole('Admin'): " . ($user->hasRole('Admin') ? 'YES' : 'NO') . "\n";
}

if ($adminUser) {
    echo "Admin hasRole('Admin'): " . ($adminUser->hasRole('Admin') ? 'YES' : 'NO') . "\n";
    echo "Admin hasRole('Super Admin'): " . ($adminUser->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
}

echo "\nTest Complete\n";
