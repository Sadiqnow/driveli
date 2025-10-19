<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use App\Http\Middleware\RolePermissionMiddleware;
use App\Models\AdminUser;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing Middleware Access Control\n";
echo "=================================\n\n";

// Create a mock request
$request = new Request();
$request->server->set('REQUEST_METHOD', 'GET');
$request->server->set('REQUEST_URI', '/admin/drivers');
$request->server->set('REMOTE_ADDR', '127.0.0.1');

// Test middleware with different users
$testCases = [
    [
        'user' => 'Super Admin',
        'role' => 'Admin',
        'permission' => 'manage_drivers',
        'expected' => 'ALLOWED'
    ],
    [
        'user' => 'Super Admin',
        'role' => 'Super Admin',
        'permission' => 'manage_users',
        'expected' => 'ALLOWED'
    ],
    [
        'user' => 'Admin',
        'role' => 'Admin',
        'permission' => 'manage_drivers',
        'expected' => 'DENIED'
    ],
    [
        'user' => 'Admin',
        'role' => 'Super Admin',
        'permission' => 'manage_users',
        'expected' => 'DENIED'
    ],
];

foreach ($testCases as $case) {
    $user = AdminUser::where('role', $case['user'])->first();

    if ($user) {
        echo "Testing: {$case['user']} accessing {$case['role']} + {$case['permission']}\n";

        // Mock authentication
        auth('admin')->login($user);

        $middleware = new RolePermissionMiddleware();

        try {
            $response = $middleware->handle($request, function($req) {
                return response('OK');
            }, $case['role'], $case['permission']);

            if ($response->getStatusCode() === 200) {
                $result = '✅ ALLOWED';
            } else {
                $result = '❌ DENIED (' . $response->getStatusCode() . ')';
            }
        } catch (\Exception $e) {
            $result = '❌ ERROR: ' . $e->getMessage();
        }

        echo "Result: {$result}\n";
        echo "Expected: {$case['expected']}\n";
        echo "Match: " . (($result === '✅ ALLOWED' && $case['expected'] === 'ALLOWED') || ($result !== '✅ ALLOWED' && $case['expected'] === 'DENIED') ? '✅' : '❌') . "\n\n";

        auth('admin')->logout();
    }
}

echo "Middleware Test Complete\n";
