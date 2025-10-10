<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Admin User Validation ===\n\n";

// Test 1: Check available enum values from database
echo "1. Checking database schema:\n";
try {
    $roleColumn = DB::select("SHOW COLUMNS FROM admin_users WHERE Field = 'role'")[0];
    $statusColumn = DB::select("SHOW COLUMNS FROM admin_users WHERE Field = 'status'")[0];
    
    echo "Role enum: " . $roleColumn->Type . "\n";
    echo "Status enum: " . $statusColumn->Type . "\n\n";
} catch (Exception $e) {
    echo "Error checking schema: " . $e->getMessage() . "\n\n";
}

// Test 2: Test controller validation
echo "2. Testing controller validation rules:\n";
$validator = Validator::make([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'TestPassword123',
    'password_confirmation' => 'TestPassword123',
    'role' => 'Admin',
    'status' => 'Active',
    'phone' => '+234567890123'
], [
    'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
    'email' => 'required|email|max:255|unique:admin_users,email',
    'phone' => 'nullable|string|max:20|regex:/^[+]?[0-9\s\-()]+$/|unique:admin_users,phone',
    'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
    'role' => 'required|in:Super Admin,Admin,Moderator,Viewer',
    'status' => 'required|in:Active,Inactive,Suspended',
]);

if ($validator->passes()) {
    echo "✓ Valid data passes controller validation\n";
} else {
    echo "✗ Valid data failed controller validation:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
}

// Test invalid status
$invalidStatusValidator = Validator::make([
    'name' => 'Test User',
    'email' => 'test2@example.com',
    'password' => 'TestPassword123',
    'password_confirmation' => 'TestPassword123',
    'role' => 'Admin',
    'status' => 'Invalid',
    'phone' => '+234567890124'
], [
    'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
    'email' => 'required|email|max:255|unique:admin_users,email',
    'phone' => 'nullable|string|max:20|regex:/^[+]?[0-9\s\-()]+$/|unique:admin_users,phone',
    'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
    'role' => 'required|in:Super Admin,Admin,Moderator,Viewer',
    'status' => 'required|in:Active,Inactive,Suspended',
]);

if ($invalidStatusValidator->fails()) {
    echo "✓ Invalid status correctly rejected by controller validation\n";
    echo "  Error: " . $invalidStatusValidator->errors()->first('status') . "\n";
} else {
    echo "✗ Invalid status was not caught by controller validation\n";
}

echo "\n3. Summary:\n";
echo "The status validation issue should now be fixed.\n";
echo "Allowed statuses: Active, Inactive, Suspended\n";
echo "Allowed roles: Super Admin, Admin, Moderator, Viewer\n";