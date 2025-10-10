<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a test request
$request = Illuminate\Http\Request::create('/test', 'GET');
$response = $kernel->handle($request);

try {
    // Test AdminUser creation with valid data
    $adminUser = new \App\Models\AdminUser([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'TestPassword123',
        'role' => 'Admin',
        'status' => 'Active',
    ]);
    
    echo "✓ AdminUser model creation successful with valid data\n";
    echo "Name: " . $adminUser->name . "\n";
    echo "Email: " . $adminUser->email . "\n";
    echo "Role: " . $adminUser->role . "\n";
    echo "Status: " . $adminUser->status . "\n\n";
    
    // Test with invalid status
    try {
        $invalidUser = new \App\Models\AdminUser([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => 'TestPassword123',
            'role' => 'Admin',
            'status' => 'Invalid',
        ]);
        
        // Try to save to trigger validation
        $invalidUser->save();
        echo "✗ Should have failed with invalid status\n";
        
    } catch (\InvalidArgumentException $e) {
        echo "✓ Correctly caught invalid status: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "✓ Invalid status caught: " . $e->getMessage() . "\n";
    }
    
    // Test with invalid role
    try {
        $invalidRoleUser = new \App\Models\AdminUser([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
            'password' => 'TestPassword123',
            'role' => 'InvalidRole',
            'status' => 'Active',
        ]);
        
        $invalidRoleUser->save();
        echo "✗ Should have failed with invalid role\n";
        
    } catch (\InvalidArgumentException $e) {
        echo "✓ Correctly caught invalid role: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "✓ Invalid role caught: " . $e->getMessage() . "\n";
    }
    
    // Check available enum values by checking database schema
    echo "\n--- Database Schema Check ---\n";
    $columns = DB::select("SHOW COLUMNS FROM admin_users WHERE Field IN ('role', 'status')");
    foreach ($columns as $column) {
        echo $column->Field . ": " . $column->Type . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}