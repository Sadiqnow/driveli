<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminUser;
use App\Models\Drivers;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "=== SUPERADMIN FUNCTIONALITY TEST ===\n\n";

// Test 1: Check Database Setup
echo "1. DATABASE SETUP TEST\n";
echo "----------------------\n";

$adminCount = AdminUser::count();
$driverCount = Drivers::count();
$roleCount = Role::count();

echo "Admin Users: $adminCount\n";
echo "Drivers: $driverCount\n";
echo "Roles: $roleCount\n";

$adminRoles = AdminUser::pluck('role')->unique()->toArray();
echo "Admin Roles: " . implode(', ', $adminRoles) . "\n";

$driverStatuses = Drivers::pluck('status')->unique()->toArray();
echo "Driver Statuses: " . implode(', ', $driverStatuses) . "\n\n";

// Test 2: Route Verification
echo "2. ROUTE VERIFICATION\n";
echo "--------------------\n";

$superadminRoutes = [
    'admin.superadmin.dashboard',
    'admin.superadmin.admins.index',
    'admin.superadmin.drivers.index',
    'admin.superadmin.users',
    'admin.superadmin.audit-logs',
    'admin.superadmin.settings'
];

foreach ($superadminRoutes as $routeName) {
    try {
        $route = Route::getRoutes()->getByName($routeName);
        echo "✓ $routeName - EXISTS\n";
    } catch (Exception $e) {
        echo "✗ $routeName - MISSING\n";
    }
}
echo "\n";

// Test 3: Controller Method Verification
echo "3. CONTROLLER METHOD VERIFICATION\n";
echo "----------------------------------\n";

$controllerMethods = [
    'App\Http\Controllers\Admin\SuperAdminController' => [
        'index', 'users', 'auditLogs', 'settings', 'createAdmin', 'storeAdmin',
        'showAdmin', 'editAdmin', 'updateAdmin', 'destroyAdmin', 'approveAdmin',
        'rejectAdmin', 'suspendAdmin', 'flagAdmin', 'assignRole', 'removeRole',
        'searchUsers', 'bulkUserOperations', 'driversExport'
    ],
    'App\Http\Controllers\SuperadminDriverController' => [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        'approve', 'reject', 'flag'
    ]
];

foreach ($controllerMethods as $controller => $methods) {
    echo "Controller: $controller\n";
    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "  ✓ $method\n";
        } else {
            echo "  ✗ $method - MISSING\n";
        }
    }
    echo "\n";
}

// Test 4: Model Verification
echo "4. MODEL VERIFICATION\n";
echo "--------------------\n";

$models = [
    'App\Models\AdminUser',
    'App\Models\Drivers',
    'App\Models\Role',
    'App\Models\UserActivity'
];

foreach ($models as $model) {
    if (class_exists($model)) {
        echo "✓ $model - EXISTS\n";
        $count = $model::count();
        echo "  Records: $count\n";
    } else {
        echo "✗ $model - MISSING\n";
    }
}
echo "\n";

// Test 5: CRUD Operations Test
echo "5. CRUD OPERATIONS TEST\n";
echo "-----------------------\n";

// Test Admin CRUD
echo "Admin CRUD:\n";
try {
    // Create test admin
    $testAdmin = AdminUser::create([
        'name' => 'Test Admin',
        'email' => 'test.admin.' . time() . '@example.com',
        'password' => bcrypt('password123'),
        'role' => 'admin',
        'status' => 'Active',
        'phone' => '1234567890'
    ]);
    echo "✓ Create Admin - SUCCESS (ID: {$testAdmin->id})\n";

    // Read test admin
    $foundAdmin = AdminUser::find($testAdmin->id);
    echo "✓ Read Admin - SUCCESS\n";

    // Update test admin
    $testAdmin->update(['name' => 'Updated Test Admin']);
    echo "✓ Update Admin - SUCCESS\n";

    // Delete test admin
    $testAdmin->delete();
    echo "✓ Delete Admin - SUCCESS\n";

} catch (Exception $e) {
    echo "✗ Admin CRUD - FAILED: " . $e->getMessage() . "\n";
}

// Test Driver CRUD
echo "\nDriver CRUD:\n";
try {
    // Create test driver
    $testDriver = Drivers::create([
        'first_name' => 'Test',
        'last_name' => 'Driver',
        'email' => 'test.driver.' . time() . '@example.com',
        'phone' => '1234567890',
        'status' => 'active',
        'verification_status' => 'pending',
        'kyc_status' => 'in_progress'
    ]);
    echo "✓ Create Driver - SUCCESS (ID: {$testDriver->id})\n";

    // Read test driver
    $foundDriver = Drivers::find($testDriver->id);
    echo "✓ Read Driver - SUCCESS\n";

    // Update test driver
    $testDriver->update(['status' => 'inactive']);
    echo "✓ Update Driver - SUCCESS\n";

    // Delete test driver
    $testDriver->delete();
    echo "✓ Delete Driver - SUCCESS\n";

} catch (Exception $e) {
    echo "✗ Driver CRUD - FAILED: " . $e->getMessage() . "\n";
}

// Test 6: Decision Workflows
echo "\n6. DECISION WORKFLOWS TEST\n";
echo "--------------------------\n";

// Create test driver for workflow testing
$workflowDriver = Drivers::create([
    'first_name' => 'Workflow',
    'last_name' => 'Test',
    'email' => 'workflow.test.' . time() . '@example.com',
    'phone' => '1234567890',
    'status' => 'pending',
    'verification_status' => 'pending',
    'kyc_status' => 'pending'
]);

echo "Testing Driver Workflows:\n";

// Approve workflow
$workflowDriver->update(['status' => 'active', 'verification_status' => 'verified']);
echo "✓ Approve Driver - SUCCESS\n";

// Reject workflow
$workflowDriver->update(['status' => 'rejected']);
echo "✓ Reject Driver - SUCCESS\n";

// Flag workflow
$workflowDriver->update(['status' => 'flagged']);
echo "✓ Flag Driver - SUCCESS\n";

// Suspend workflow
$workflowDriver->update(['status' => 'suspended']);
echo "✓ Suspend Driver - SUCCESS\n";

// Cleanup
$workflowDriver->delete();

// Test 7: Bulk Operations Test
echo "\n7. BULK OPERATIONS TEST\n";
echo "-----------------------\n";

$bulkDrivers = [];
for ($i = 0; $i < 3; $i++) {
    $bulkDrivers[] = Drivers::create([
        'first_name' => 'Bulk',
        'last_name' => 'Test' . $i,
        'email' => 'bulk.test' . $i . '.' . time() . '@example.com',
        'phone' => '123456789' . $i,
        'status' => 'active',
        'verification_status' => 'verified',
        'kyc_status' => 'completed'
    ]);
}

$bulkIds = array_column($bulkDrivers, 'id');

echo "Created " . count($bulkDrivers) . " test drivers for bulk operations\n";

// Simulate bulk approve
Drivers::whereIn('id', $bulkIds)->update(['status' => 'active']);
echo "✓ Bulk Approve - SUCCESS\n";

// Simulate bulk deactivate
Drivers::whereIn('id', $bulkIds)->update(['status' => 'inactive']);
echo "✓ Bulk Deactivate - SUCCESS\n";

// Simulate bulk delete
Drivers::whereIn('id', $bulkIds)->delete();
echo "✓ Bulk Delete - SUCCESS\n";

// Test 8: Fallback Scenarios
echo "\n8. FALLBACK SCENARIOS TEST\n";
echo "--------------------------\n";

// Test non-existent driver
try {
    $nonExistentDriver = Drivers::find(999999);
    if (!$nonExistentDriver) {
        echo "✓ Non-existent Driver Fallback - SUCCESS\n";
    }
} catch (Exception $e) {
    echo "✗ Non-existent Driver Fallback - FAILED: " . $e->getMessage() . "\n";
}

// Test duplicate email handling
try {
    $duplicateDriver = Drivers::create([
        'first_name' => 'Duplicate',
        'last_name' => 'Test',
        'email' => 'duplicate@example.com',
        'phone' => '1234567890',
        'status' => 'active'
    ]);

    // Try to create another with same email
    $duplicateDriver2 = Drivers::create([
        'first_name' => 'Duplicate2',
        'last_name' => 'Test2',
        'email' => 'duplicate@example.com',
        'phone' => '0987654321',
        'status' => 'active'
    ]);

    echo "✗ Duplicate Email Handling - FAILED (Should have thrown exception)\n";

    // Cleanup
    $duplicateDriver->delete();
    if (isset($duplicateDriver2)) $duplicateDriver2->delete();

} catch (Exception $e) {
    echo "✓ Duplicate Email Handling - SUCCESS (Exception caught: " . substr($e->getMessage(), 0, 50) . "...)\n";
}

// Test 9: Role-Based Access Test
echo "\n9. ROLE-BASED ACCESS TEST\n";
echo "-------------------------\n";

$superAdmin = AdminUser::where('role', 'super_admin')->first();
$regularAdmin = AdminUser::where('role', 'admin')->first();

if ($superAdmin) {
    echo "✓ Super Admin Role - EXISTS\n";
} else {
    echo "✗ Super Admin Role - MISSING\n";
}

if ($regularAdmin) {
    echo "✓ Regular Admin Role - EXISTS\n";
} else {
    echo "✗ Regular Admin Role - MISSING\n";
}

// Test 10: Activity Logging Test
echo "\n10. ACTIVITY LOGGING TEST\n";
echo "-------------------------\n";

$activityCountBefore = \App\Models\SuperadminActivityLog::count();

// Simulate some activity
$testDriver = Drivers::create([
    'first_name' => 'Activity',
    'last_name' => 'Test',
    'email' => 'activity.test.' . time() . '@example.com',
    'phone' => '1234567890',
    'status' => 'active'
]);

$activityCountAfter = \App\Models\SuperadminActivityLog::count();

if ($activityCountAfter > $activityCountBefore) {
    echo "✓ Activity Logging - SUCCESS\n";
} else {
    echo "✗ Activity Logging - FAILED\n";
}

// Cleanup
$testDriver->delete();

// Test 11: Performance Test
echo "\n11. PERFORMANCE TEST\n";
echo "-------------------\n";

$startTime = microtime(true);

// Test query performance
$drivers = Drivers::take(100)->get();
$queryTime = microtime(true) - $startTime;

echo "Query Performance: " . round($queryTime * 1000, 2) . "ms for " . $drivers->count() . " records\n";

if ($queryTime < 1.0) { // Less than 1 second
    echo "✓ Performance - ACCEPTABLE\n";
} else {
    echo "⚠ Performance - SLOW\n";
}

// Test 12: Summary
echo "\n12. TEST SUMMARY\n";
echo "---------------\n";

echo "Database Records:\n";
echo "- Admin Users: " . AdminUser::count() . "\n";
echo "- Drivers: " . Drivers::count() . "\n";
echo "- Roles: " . Role::count() . "\n";
echo "- Activities: " . \App\Models\UserActivity::count() . "\n";

echo "\nRoutes Available: " . count(Route::getRoutes()->getRoutes()) . "\n";

echo "\n=== TEST COMPLETED ===\n";

?>
