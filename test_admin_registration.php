<?php

// Test admin registration system
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

try {
    // Load Laravel app
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Test database connection
    echo "Testing database connection...\n";
    try {
        DB::connection()->getPdo();
        echo "✓ Database connection successful\n";
    } catch (Exception $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    // Test admin_users table exists
    echo "\nTesting admin_users table...\n";
    try {
        $count = DB::table('admin_users')->count();
        echo "✓ admin_users table exists with {$count} records\n";
    } catch (Exception $e) {
        echo "✗ admin_users table error: " . $e->getMessage() . "\n";
    }
    
    // Test required services exist
    echo "\nTesting required services...\n";
    try {
        $authService = $app->make(\App\Services\AuthenticationService::class);
        echo "✓ AuthenticationService loaded\n";
    } catch (Exception $e) {
        echo "✗ AuthenticationService failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $validationService = $app->make(\App\Services\ValidationService::class);
        echo "✓ ValidationService loaded\n";
    } catch (Exception $e) {
        echo "✗ ValidationService failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $errorService = $app->make(\App\Services\ErrorHandlingService::class);
        echo "✓ ErrorHandlingService loaded\n";
    } catch (Exception $e) {
        echo "✗ ErrorHandlingService failed: " . $e->getMessage() . "\n";
    }
    
    // Test admin controller
    echo "\nTesting AdminAuthController...\n";
    try {
        $controller = $app->make(\App\Http\Controllers\Admin\AdminAuthController::class);
        echo "✓ AdminAuthController loaded\n";
    } catch (Exception $e) {
        echo "✗ AdminAuthController failed: " . $e->getMessage() . "\n";
    }
    
    // Test registration validation
    echo "\nTesting registration validation...\n";
    $testData = [
        'name' => 'Test Admin',
        'email' => 'test@example.com',
        'password' => 'TestPass123!',
        'password_confirmation' => 'TestPass123!',
        'phone' => '+2341234567890'
    ];
    
    try {
        $request = new \Illuminate\Http\Request();
        $request->merge($testData);
        
        $validationService = $app->make(\App\Services\ValidationService::class);
        $validated = $validationService->validateAdminRegistration($request);
        echo "✓ Validation passed\n";
    } catch (Exception $e) {
        echo "✗ Validation failed: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
}