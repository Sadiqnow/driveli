<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Admin Registration Debug ===\n";
    
    // Check admin count
    $adminCount = DB::table('admin_users')->count();
    echo "Current admin count: $adminCount\n";
    
    // Check environment
    $env = app()->environment();
    echo "Environment: $env\n";
    
    // Check registration policy
    $registrationAllowed = $adminCount === 0 || app()->environment(['local', 'testing']);
    echo "Registration allowed: " . ($registrationAllowed ? 'Yes' : 'No') . "\n";
    
    if (!$registrationAllowed) {
        echo "\n=== Issue Found ===\n";
        echo "Registration is disabled because:\n";
        echo "- Admin count: $adminCount (not zero)\n";
        echo "- Environment: $env (not local/testing)\n";
        echo "\nTo fix this, you need to either:\n";
        echo "1. Set APP_ENV=local in your .env file, OR\n";
        echo "2. Use the artisan command: php artisan admin:create-super\n";
    } else {
        echo "\nRegistration should be allowed. Let's test creating an admin...\n";
        
        // Test admin creation directly
        $testAdmin = DB::table('admin_users')->insert([
            'name' => 'Test Super Admin',
            'email' => 'test@admin.com',
            'password' => Hash::make('testpassword123'),
            'role' => 'Super Admin',
            'status' => 'Active',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($testAdmin) {
            echo "✅ Test admin created successfully!\n";
            echo "Email: test@admin.com\n";
            echo "Password: testpassword123\n";
        } else {
            echo "❌ Failed to create test admin\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}