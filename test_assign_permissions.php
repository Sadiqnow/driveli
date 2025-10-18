<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Http\Controllers\Admin\SuperAdminController;

echo "Testing assignPermissionsToRole method\n";
echo "=====================================\n\n";

// Test 1: Check if roles and permissions exist
echo "Test 1: Checking database setup\n";
$role = Role::where('name', 'admin')->first();
if (!$role) {
    echo "❌ Admin role not found. Please run seeders first.\n";
    exit(1);
}
echo "✅ Admin role found: {$role->display_name}\n";

$currentPermissions = $role->permissions()->pluck('permissions.name')->toArray();
echo "Current permissions count: " . count($currentPermissions) . "\n";
if (count($currentPermissions) > 0) {
    echo "Sample permissions: " . implode(', ', array_slice($currentPermissions, 0, 3)) . "\n";
}

// Test 2: Get some permission IDs
$permissions = Permission::where('is_active', true)->limit(3)->get();
if ($permissions->isEmpty()) {
    echo "❌ No active permissions found. Please run seeders first.\n";
    exit(1);
}

$permissionIds = $permissions->pluck('id')->toArray();
echo "✅ Found " . count($permissionIds) . " test permissions: " . implode(', ', $permissionIds) . "\n";

// Test 3: Test the method with valid data
echo "\nTest 3: Testing assignPermissionsToRole method\n";
try {
    $request = new Request();
    $request->merge([
        'role_id' => $role->id,
        'permission_ids' => $permissionIds,
        'notes' => 'Automated testing of permission assignment'
    ]);

    $controller = new SuperAdminController();
    $response = $controller->assignPermissionsToRole($request);

    if ($response->getStatusCode() === 200) {
        $responseData = json_decode($response->getContent(), true);
        if ($responseData['success']) {
            echo "✅ Method executed successfully\n";
            echo "Response message: {$responseData['message']}\n";
            echo "Added permissions: {$responseData['data']['added_count']}\n";
            echo "Removed permissions: {$responseData['data']['removed_count']}\n";
        } else {
            echo "❌ Method returned error: {$responseData['message']}\n";
        }
    } else {
        echo "❌ HTTP error: {$response->getStatusCode()}\n";
    }
} catch (Exception $e) {
    echo "❌ Exception thrown: {$e->getMessage()}\n";
}

// Test 4: Test validation with invalid role_id
echo "\nTest 4: Testing validation with invalid role_id\n";
try {
    $request = new Request();
    $request->merge([
        'role_id' => 99999, // Invalid role ID
        'permission_ids' => $permissionIds,
    ]);

    $controller = new SuperAdminController();
    $response = $controller->assignPermissionsToRole($request);
    echo "❌ Should have thrown validation error but didn't\n";
} catch (Illuminate\Validation\ValidationException $e) {
    echo "✅ Validation correctly caught invalid role_id\n";
} catch (Exception $e) {
    echo "❌ Unexpected exception: {$e->getMessage()}\n";
}

// Test 5: Test validation with invalid permission_ids
echo "\nTest 5: Testing validation with invalid permission_ids\n";
try {
    $request = new Request();
    $request->merge([
        'role_id' => $role->id,
        'permission_ids' => [99999], // Invalid permission ID
    ]);

    $controller = new SuperAdminController();
    $response = $controller->assignPermissionsToRole($request);
    echo "❌ Should have thrown validation error but didn't\n";
} catch (Illuminate\Validation\ValidationException $e) {
    echo "✅ Validation correctly caught invalid permission_ids\n";
} catch (Exception $e) {
    echo "❌ Unexpected exception: {$e->getMessage()}\n";
}

// Test 6: Verify sync functionality
echo "\nTest 6: Verifying sync functionality\n";
$role->refresh();
$newPermissions = $role->permissions()->pluck('permissions.name')->toArray();
echo "Permissions after sync: " . count($newPermissions) . "\n";
if (count($newPermissions) === count($permissionIds)) {
    echo "✅ Sync correctly set exact number of permissions\n";
} else {
    echo "❌ Sync did not set correct number of permissions\n";
}

echo "\nTesting completed!\n";
