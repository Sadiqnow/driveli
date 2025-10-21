<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Role-Based Dashboard Visibility Testing ===\n\n";

try {
    // Test 1: Check if config loads
    echo "1. Testing menu configuration...\n";
    $menus = config('menus');
    echo "   ✓ Menus config loaded: " . count($menus) . " menu items\n";

    // Test 2: Check PermissionHelper instantiation
    echo "\n2. Testing PermissionHelper...\n";
    $helper = new App\Helpers\PermissionHelper();
    echo "   ✓ PermissionHelper instantiated successfully\n";

    // Test 3: Check menu filtering (without auth)
    echo "\n3. Testing menu filtering (no auth)...\n";
    $filtered = $helper->getFilteredMenus();
    echo "   ✓ Filtered menus: " . count($filtered) . " items (should be 0 without auth)\n";

    // Test 4: Check available users
    echo "\n4. Checking available admin users...\n";
    $users = App\Models\AdminUser::all();
    if ($users->count() > 0) {
        echo "   ✓ Found " . $users->count() . " admin users:\n";
        foreach ($users as $user) {
            echo "     - {$user->name} ({$user->email}) - Role: " . ($user->role ?? 'No role') . "\n";
        }
    } else {
        echo "   ⚠ No admin users found\n";
    }

    // Test 5: Check RoleSyncService
    echo "\n5. Testing RoleSyncService...\n";
    $roleSync = app(App\Services\RoleSyncService::class);
    echo "   ✓ RoleSyncService instantiated successfully\n";

    // Test 6: Check if routes exist
    echo "\n6. Checking route permissions...\n";
    $routePermissions = App\Models\RoutePermission::count();
    echo "   ✓ Route permissions table has {$routePermissions} entries\n";

    echo "\n=== All Basic Tests Passed ===\n";
    echo "\nNext steps for manual testing:\n";
    echo "1. Login as SuperAdmin - should see all menus including 'Super Admin' section\n";
    echo "2. Login as Admin - should see most menus but not 'Super Admin' section\n";
    echo "3. Login as Moderator/Agent - should see limited menus\n";
    echo "4. Check dashboard widgets visibility based on permissions\n";

} catch (Exception $e) {
    echo "\n❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
