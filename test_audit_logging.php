<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use App\Http\Middleware\RolePermissionMiddleware;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Log;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing Audit Logging System\n";
echo "============================\n\n";

// Test audit logging by triggering middleware denials
$request = new Request();
$request->server->set('REQUEST_METHOD', 'GET');
$request->server->set('REQUEST_URI', '/admin/drivers');
$request->server->set('REMOTE_ADDR', '127.0.0.1');
$request->headers->set('User-Agent', 'Test Browser/1.0');

// Test with Admin user trying to access Super Admin route
$adminUser = AdminUser::where('role', 'Admin')->first();

if ($adminUser) {
    echo "Testing audit logging with Admin user accessing Super Admin route...\n";

    // Mock authentication
    auth('admin')->login($adminUser);

    $middleware = new RolePermissionMiddleware();

    try {
        // This should be denied and logged
        $response = $middleware->handle($request, function($req) {
            return response('OK');
        }, 'Super Admin', 'manage_users');

        echo "Response Status: " . $response->getStatusCode() . "\n";

        if ($response->getStatusCode() === 302) { // Redirect for web request
            echo "✅ Access correctly denied\n";
        }

    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }

    auth('admin')->logout();
}

// Check if audit_logs table exists and has entries
try {
    $auditLogCount = \DB::table('audit_logs')->count();
    echo "\nAudit Logs Table Status:\n";
    echo "Total audit log entries: {$auditLogCount}\n";

    if ($auditLogCount > 0) {
        $recentLogs = \DB::table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        echo "\nRecent Audit Log Entries:\n";
        foreach ($recentLogs as $log) {
            echo "- " . date('Y-m-d H:i:s', strtotime($log->created_at)) . ": {$log->action} - {$log->description}\n";
        }
    }

} catch (\Exception $e) {
    echo "\n❌ Audit logs table not accessible: " . $e->getMessage() . "\n";
}

// Check Laravel logs for middleware warnings
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    echo "\nLaravel Log Status:\n";
    $logContent = file_get_contents($logPath);
    $middlewareLogs = substr_count($logContent, 'RolePermissionMiddleware Access Denied');

    echo "Middleware access denial logs in Laravel log: {$middlewareLogs}\n";

    if ($middlewareLogs > 0) {
        echo "✅ Middleware logging is working\n";
    } else {
        echo "⚠️  No middleware logs found (may be normal if logs rotated)\n";
    }
} else {
    echo "\n❌ Laravel log file not found\n";
}

echo "\nAudit Logging Test Complete\n";
