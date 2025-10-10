<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Http\Request;

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set up environment
$_ENV['APP_ENV'] = 'local';
$_ENV['APP_KEY'] = 'base64:WyEmco4weWvjDur0HTGb+5u9w+bpXKIDp8DtscLoPYs=';
$_ENV['DB_CONNECTION'] = 'mysql';
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_DATABASE'] = 'drivelink_db';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = '';

// Create a fake HTTP request
$request = Request::create('/driver/register', 'GET');

try {
    // Handle the request
    $response = $kernel->handle($request);
    
    echo "Registration page status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Driver registration page loads successfully\n";
        
        // Test form submission
        $postRequest = Request::create('/driver/register', 'POST', [
            'drivers_license_number' => 'TEST123456',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'Test',
            'surname' => 'Driver',
            'phone' => '08012345678',
            'email' => 'test_driver_' . time() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
            '_token' => 'test-token'
        ]);
        
        // Add CSRF token middleware exception for testing
        $postRequest->session()->put('_token', 'test-token');
        
        echo "Testing registration form submission...\n";
        
        $postResponse = $kernel->handle($postRequest);
        echo "Form submission status: " . $postResponse->getStatusCode() . "\n";
        
        if ($postResponse->getStatusCode() === 302) {
            echo "✅ Driver registration form processes successfully (redirect response)\n";
        } else {
            echo "❌ Registration form submission failed\n";
            echo "Response content:\n" . substr($postResponse->getContent(), 0, 500) . "\n";
        }
        
    } else {
        echo "❌ Registration page failed to load\n";
        echo "Response content:\n" . substr($response->getContent(), 0, 500) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing driver registration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test database connectivity
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    
    // Check if drivers table exists
    $stmt = $pdo->query("DESCRIBE drivers");
    if ($stmt) {
        echo "✅ Database connection successful\n";
        echo "✅ drivers table exists\n";
        
        // Check table structure
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredFields = ['drivers_license_number', 'first_name', 'surname', 'email', 'phone', 'password'];
        $missingFields = array_diff($requiredFields, $columns);
        
        if (empty($missingFields)) {
            echo "✅ All required registration fields exist in table\n";
        } else {
            echo "❌ Missing required fields: " . implode(', ', $missingFields) . "\n";
        }
        
    } else {
        echo "❌ drivers table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== DRIVER REGISTRATION TEST COMPLETE ===\n";