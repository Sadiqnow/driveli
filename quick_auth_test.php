<?php

require_once 'vendor/autoload.php';

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "🚛 Drivelink Authentication Test\n";
    echo "===============================\n\n";

    // Check if AdminUser model works
    $adminCount = App\Models\AdminUser::count();
    echo "✅ Admin users in database: $adminCount\n";

    if ($adminCount === 0) {
        echo "⚠️  No admin users found - you'll need to run seeders\n";
    } else {
        echo "✅ Admin users exist - login should work\n";
    }

    // Test authentication guard
    $guards = config('auth.guards');
    if (isset($guards['admin'])) {
        echo "✅ Admin guard configured\n";
    } else {
        echo "❌ Admin guard missing\n";
    }

    // Check routes
    $routes = app('router')->getRoutes();
    $adminLoginRoute = null;
    foreach ($routes as $route) {
        if ($route->getName() === 'admin.login') {
            $adminLoginRoute = $route;
            break;
        }
    }

    if ($adminLoginRoute) {
        echo "✅ Admin login route configured\n";
    } else {
        echo "❌ Admin login route missing\n";
    }

    echo "\n📋 Next Steps:\n";
    if ($adminCount === 0) {
        echo "1. Run: php artisan db:seed --class=AdminUserSeeder\n";
    }
    echo "2. Access /admin/login\n";
    echo "3. Use credentials: admin@drivelink.com / password123\n";

    echo "\n✅ Authentication test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}