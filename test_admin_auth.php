<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ADMIN AUTHENTICATION DEBUG ===" . PHP_EOL;

try {
    // Check database connection
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connection successful" . PHP_EOL;
    
    // Check if admin_users table exists
    $tables = DB::select("SHOW TABLES LIKE 'admin_users'");
    if (empty($tables)) {
        echo "✗ admin_users table does not exist!" . PHP_EOL;
        exit(1);
    }
    echo "✓ admin_users table exists" . PHP_EOL;
    
    // Check admin users
    $admins = DB::table('admin_users')->get();
    echo "Total admin users: " . $admins->count() . PHP_EOL;
    
    if ($admins->count() > 0) {
        echo "\nAdmin Users Found:" . PHP_EOL;
        foreach ($admins as $admin) {
            echo "- ID: {$admin->id}, Email: {$admin->email}, Status: {$admin->status}" . PHP_EOL;
            echo "  Password Hash: " . substr($admin->password, 0, 20) . "..." . PHP_EOL;
            echo "  Created: {$admin->created_at}" . PHP_EOL;
            
            // Test password hashing for this user
            if ($admin->email) {
                $testPassword = 'password123'; // Replace with actual password being tested
                echo "  Testing password '$testPassword': ";
                if (Hash::check($testPassword, $admin->password)) {
                    echo "✓ MATCHES" . PHP_EOL;
                } else {
                    echo "✗ NO MATCH" . PHP_EOL;
                }
            }
        }
    }
    
    // Test the AdminUser model
    echo "\n=== TESTING ADMINUSER MODEL ===" . PHP_EOL;
    $adminUsers = App\Models\AdminUser::all();
    echo "AdminUser model count: " . $adminUsers->count() . PHP_EOL;
    
    // Test authentication attempt
    echo "\n=== TESTING AUTH ATTEMPT ===" . PHP_EOL;
    
    if ($adminUsers->count() > 0) {
        $testAdmin = $adminUsers->first();
        echo "Testing with admin: " . $testAdmin->email . PHP_EOL;
        
        // Test with common passwords
        $testPasswords = ['password', 'password123', 'admin', 'secret'];
        
        foreach ($testPasswords as $testPassword) {
            $credentials = ['email' => $testAdmin->email, 'password' => $testPassword];
            
            echo "Testing password '$testPassword': ";
            if (Auth::guard('admin')->attempt($credentials)) {
                echo "✓ SUCCESS!" . PHP_EOL;
                Auth::guard('admin')->logout();
            } else {
                echo "✗ FAILED" . PHP_EOL;
            }
        }
    }
    
    // Check auth configuration
    echo "\n=== AUTH CONFIGURATION ===" . PHP_EOL;
    $authConfig = config('auth');
    echo "Admin guard configured: " . (isset($authConfig['guards']['admin']) ? '✓ YES' : '✗ NO') . PHP_EOL;
    echo "Admin provider configured: " . (isset($authConfig['providers']['admin_users']) ? '✓ YES' : '✗ NO') . PHP_EOL;
    
    if (isset($authConfig['providers']['admin_users'])) {
        echo "Admin model: " . $authConfig['providers']['admin_users']['model'] . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}

echo "\n=== DEBUG COMPLETE ===" . PHP_EOL;