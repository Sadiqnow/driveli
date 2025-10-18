<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\Request;

echo "Testing manageUserRoles method...\n";

// Get test data
$user = AdminUser::first();
$roles = Role::take(2)->get();

if (!$user || $roles->isEmpty()) {
    echo "Test data not available. Please ensure users and roles exist.\n";
    exit(1);
}

echo "Test User: {$user->name} (ID: {$user->id})\n";
echo "Available Roles: " . $roles->pluck('name')->join(', ') . "\n";

// Test the method
$controller = app(\App\Http\Controllers\Admin\SuperAdminController::class);
$request = new Request();
$request->merge([
    'user_id' => $user->id,
    'role_ids' => $roles->pluck('id')->toArray(),
    'notes' => 'Test role sync'
]);

try {
    $response = $controller->manageUserRoles($request);
    $data = json_decode($response->getContent(), true);

    echo "Response: " . ($data['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($data['success']) {
        echo "Message: {$data['message']}\n";
        echo "Added: {$data['data']['added_count']} roles\n";
        echo "Removed: {$data['data']['removed_count']} roles\n";
    } else {
        echo "Error: {$data['message']}\n";
    }
} catch (Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
}

echo "Test completed.\n";
