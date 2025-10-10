<?php

echo "Testing Login Setup...\n\n";

try {
    // Check if required files exist
    echo "1. Checking Required Files:\n";
    
    $requiredFiles = [
        'app/Models/AdminUser.php' => 'AdminUser Model',
        'app/Http/Controllers/Admin/AdminAuthController.php' => 'AdminAuthController',
        'database/seeders/AdminUserSeeder.php' => 'AdminUserSeeder',
        'resources/views/admin/login.blade.php' => 'Login View',
        'resources/views/admin/register.blade.php' => 'Registration View',
        'routes/web.php' => 'Web Routes'
    ];
    
    foreach ($requiredFiles as $file => $description) {
        if (file_exists($file)) {
            echo "   ✅ {$description} exists\n";
        } else {
            echo "   ❌ {$description} missing\n";
        }
    }
    
    echo "\n2. Checking AdminAuthController Methods:\n";
    
    $controller = 'App\Http\Controllers\Admin\AdminAuthController';
    $requiredMethods = ['showLogin', 'login', 'logout', 'showRegister', 'register'];
    
    if (class_exists($controller)) {
        echo "   ✅ AdminAuthController class exists\n";
        
        foreach ($requiredMethods as $method) {
            if (method_exists($controller, $method)) {
                echo "   ✅ Method '{$method}' exists\n";
            } else {
                echo "   ❌ Method '{$method}' missing\n";
            }
        }
    } else {
        echo "   ❌ AdminAuthController class not found\n";
    }
    
    echo "\n3. Checking Routes Configuration:\n";
    
    $routesContent = file_get_contents('routes/web.php');
    
    $requiredRoutes = [
        'admin.login' => 'Login Route',
        'admin.login.submit' => 'Login Submit Route', 
        'admin.register' => 'Register Route',
        'admin.register.submit' => 'Register Submit Route'
    ];
    
    foreach ($requiredRoutes as $route => $description) {
        if (strpos($routesContent, $route) !== false) {
            echo "   ✅ {$description} configured\n";
        } else {
            echo "   ❌ {$description} missing\n";
        }
    }
    
    echo "\n4. Setup Instructions:\n";
    echo "   📋 To get started:\n";
    echo "   1. Run migrations: php artisan migrate\n";
    echo "   2. Run seeders: php artisan db:seed\n";
    echo "   3. Or run setup script: ./setup.bat (Windows) or ./setup.sh (Linux/Mac)\n";
    echo "   4. Access /admin/login or /admin/register\n";
    
    echo "\n✅ Login setup test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}