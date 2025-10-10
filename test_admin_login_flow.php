<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use App\Services\AuthenticationService;

echo "=== TESTING ADMIN LOGIN FLOW ===\n";

try {
    // Check if admin exists
    $admin = AdminUser::where('email', 'admin@drivelink.com')->first();
    if (!$admin) {
        echo "❌ Admin user not found\n";
        exit(1);
    }
    echo "✓ Admin user found: {$admin->email}\n";

    // Test password verification
    if (Auth::guard('admin')->attempt(['email' => 'admin@drivelink.com', 'password' => 'password123'])) {
        echo "✓ Auth attempt successful\n";

        // Check if user is authenticated
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            echo "✓ User authenticated: {$user->name} ({$user->email})\n";
            echo "✓ Role: {$user->role}\n";
            echo "✓ Status: {$user->status}\n";

            // Test route access
            $request = Request::create('/admin/dashboard', 'GET');
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // Simulate middleware check
            if ($user->isActive() && $user->hasRole('Super Admin')) {
                echo "✓ User has required permissions\n";
            } else {
                echo "❌ User lacks required permissions\n";
                echo "  - isActive: " . ($user->isActive() ? 'YES' : 'NO') . "\n";
                echo "  - hasRole Super Admin: " . ($user->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
            }

        } else {
            echo "❌ User not authenticated after attempt\n";
        }

        // Logout
        Auth::guard('admin')->logout();
        echo "✓ Logged out successfully\n";

    } else {
        echo "❌ Auth attempt failed\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
