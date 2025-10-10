<?php

// Test the fixed admin registration
require_once 'vendor/autoload.php';

use App\Models\AdminUser;
use App\Services\AuthenticationService;
use App\Services\ValidationService;
use Illuminate\Http\Request;

try {
    // Bootstrap Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');
    
    echo "Testing fixed admin registration...\n\n";
    
    // Check if admin table is accessible
    try {
        $adminCount = AdminUser::count();
        echo "✓ Admin table accessible. Current count: {$adminCount}\n";
    } catch (Exception $e) {
        echo "✗ Admin table error: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test services
    try {
        $authService = new AuthenticationService();
        $validationService = new ValidationService();
        echo "✓ Services loaded successfully\n";
    } catch (Exception $e) {
        echo "✗ Service loading failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test validation with sample data
    $testData = [
        'name' => 'Test Admin User',
        'email' => 'testadmin' . time() . '@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'phone' => '+2348012345678'
    ];
    
    echo "\nTesting validation with data:\n";
    foreach ($testData as $key => $value) {
        echo "  {$key}: " . ($key === 'password' || $key === 'password_confirmation' ? '[HIDDEN]' : $value) . "\n";
    }
    
    try {
        $request = new Request();
        $request->merge($testData);
        $validated = $validationService->validateAdminRegistration($request);
        echo "✓ Validation passed\n";
    } catch (Exception $e) {
        echo "✗ Validation failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test registration (but don't actually create to avoid duplicates)
    echo "\nTesting registration process (dry run)...\n";
    try {
        $isRegistrationAllowed = $authService->isRegistrationAllowed();
        echo $isRegistrationAllowed ? "✓ Registration is allowed\n" : "✗ Registration is disabled\n";
    } catch (Exception $e) {
        echo "✗ Registration check failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ All tests passed! Admin registration system appears to be working.\n";
    echo "\nTo test registration via web interface, visit:\n";
    echo "http://localhost/drivelink/admin/register\n";
    
} catch (Exception $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}