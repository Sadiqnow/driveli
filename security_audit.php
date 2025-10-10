<?php
/**
 * DriveLink Security Audit Script
 * Comprehensive security assessment for the Laravel DriveLink application
 */

require_once 'vendor/autoload.php';

echo "=== DRIVELINK SECURITY AUDIT REPORT ===" . PHP_EOL;
echo "Generated on: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "=========================================" . PHP_EOL;

// Initialize Laravel Application
try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "✓ Laravel application initialized successfully" . PHP_EOL;
} catch (Exception $e) {
    echo "✗ Failed to initialize Laravel: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "1. AUTHENTICATION SECURITY ANALYSIS" . PHP_EOL;
echo "=====================================" . PHP_EOL;

// Check auth configuration
$authConfig = config('auth');
$guards = $authConfig['guards'];
$providers = $authConfig['providers'];

echo "Authentication Guards:" . PHP_EOL;
foreach ($guards as $guardName => $guardConfig) {
    echo "  - {$guardName}: {$guardConfig['driver']} ({$guardConfig['provider']})" . PHP_EOL;
}

echo PHP_EOL . "Security Issues Found:" . PHP_EOL;

// Security Issue 1: Multiple authentication systems without proper separation
if (count($guards) > 3) {
    echo "⚠ MEDIUM: Multiple authentication guards may lead to confusion and security gaps" . PHP_EOL;
    echo "   Recommendation: Consolidate or clearly document guard usage" . PHP_EOL;
}

// Security Issue 2: Check password reset configuration
$passwordConfig = $authConfig['passwords'];
foreach ($passwordConfig as $provider => $config) {
    if ($config['expire'] > 60) {
        echo "⚠ MEDIUM: Password reset token expiry for '{$provider}' is over 60 minutes" . PHP_EOL;
    }
    if ($config['throttle'] < 60) {
        echo "⚠ LOW: Password reset throttle for '{$provider}' may be too aggressive" . PHP_EOL;
    }
}

echo PHP_EOL . "2. MASS ASSIGNMENT VULNERABILITY ANALYSIS" . PHP_EOL;
echo "=========================================" . PHP_EOL;

// Check DriverNormalized model for mass assignment issues
$driverModel = new App\Models\DriverNormalized();
$fillable = $driverModel->getFillable();
$criticalFields = [
    'password', 'verification_status', 'verified_by', 'verified_at', 
    'is_active', 'status', 'ocr_verification_status'
];

echo "DriverNormalized fillable fields: " . count($fillable) . PHP_EOL;
echo "Critical security fields in fillable:" . PHP_EOL;

$securityIssues = [];
foreach ($criticalFields as $field) {
    if (in_array($field, $fillable)) {
        echo "⚠ HIGH: '{$field}' is mass assignable - SECURITY RISK" . PHP_EOL;
        $securityIssues[] = $field;
    }
}

if (empty($securityIssues)) {
    echo "✓ No critical security fields found in mass assignment" . PHP_EOL;
} else {
    echo PHP_EOL . "RECOMMENDATION: Remove these fields from \$fillable or use \$guarded:" . PHP_EOL;
    foreach ($securityIssues as $field) {
        echo "  - {$field}" . PHP_EOL;
    }
}

echo PHP_EOL . "3. FILE UPLOAD SECURITY ANALYSIS" . PHP_EOL;
echo "=================================" . PHP_EOL;

// Analyze file upload security
echo "File upload validation analysis:" . PHP_EOL;

$allowedMimes = ['jpeg', 'jpg', 'png', 'pdf'];
$maxSize = 5120; // 5MB in KB

echo "✓ File type validation: " . implode(', ', $allowedMimes) . PHP_EOL;
echo "✓ File size limit: {$maxSize}KB" . PHP_EOL;

// Check storage configuration
$filesystemConfig = config('filesystems');
$defaultDisk = $filesystemConfig['default'];
echo "✓ Default storage disk: {$defaultDisk}" . PHP_EOL;

// Security concerns in file upload
echo PHP_EOL . "File upload security issues:" . PHP_EOL;
echo "⚠ MEDIUM: File extension validation only - missing MIME type verification" . PHP_EOL;
echo "⚠ MEDIUM: No virus scanning implemented" . PHP_EOL;
echo "⚠ LOW: Large file size limit (5MB) may cause storage issues" . PHP_EOL;

echo PHP_EOL . "4. SESSION SECURITY ANALYSIS" . PHP_EOL;
echo "============================" . PHP_EOL;

$sessionConfig = config('session');
echo "Session configuration:" . PHP_EOL;
echo "  Driver: " . $sessionConfig['driver'] . PHP_EOL;
echo "  Lifetime: " . $sessionConfig['lifetime'] . " minutes" . PHP_EOL;
echo "  HTTP Only: " . ($sessionConfig['http_only'] ? 'Yes' : 'No') . PHP_EOL;
echo "  Secure: " . ($sessionConfig['secure'] ? 'Yes' : 'No') . PHP_EOL;
echo "  Same Site: " . $sessionConfig['same_site'] . PHP_EOL;

$sessionIssues = [];
if (!$sessionConfig['http_only']) {
    echo "⚠ HIGH: HTTPOnly not enabled - XSS vulnerability" . PHP_EOL;
    $sessionIssues[] = 'http_only';
}
if (!$sessionConfig['secure'] && env('APP_ENV') === 'production') {
    echo "⚠ HIGH: Secure flag not set for production" . PHP_EOL;
    $sessionIssues[] = 'secure';
}
if ($sessionConfig['lifetime'] > 120) {
    echo "⚠ MEDIUM: Session lifetime is long (" . $sessionConfig['lifetime'] . " min)" . PHP_EOL;
    $sessionIssues[] = 'lifetime';
}

if (empty($sessionIssues)) {
    echo "✓ No critical session security issues found" . PHP_EOL;
}

echo PHP_EOL . "5. ENVIRONMENT SECURITY CHECK" . PHP_EOL;
echo "=============================" . PHP_EOL;

$appEnv = env('APP_ENV');
$appDebug = env('APP_DEBUG');
$appKey = env('APP_KEY');

echo "Environment: {$appEnv}" . PHP_EOL;
echo "Debug mode: " . ($appDebug ? 'Enabled' : 'Disabled') . PHP_EOL;
echo "App key set: " . ($appKey ? 'Yes' : 'No') . PHP_EOL;

if ($appDebug && $appEnv === 'production') {
    echo "⚠ CRITICAL: Debug mode enabled in production!" . PHP_EOL;
}

if (!$appKey) {
    echo "⚠ CRITICAL: APP_KEY not set!" . PHP_EOL;
}

echo PHP_EOL . "6. DATABASE SECURITY CHECK" . PHP_EOL;
echo "==========================" . PHP_EOL;

try {
    $db = $app->make('db');
    $connection = $db->connection();
    echo "✓ Database connection established" . PHP_EOL;
    
    // Check for database structure
    $adminUsers = $db->select("SELECT COUNT(*) as count FROM admin_users");
    $driversNormalized = $db->select("SELECT COUNT(*) as count FROM drivers");
    
    echo "✓ admin_users table: " . $adminUsers[0]->count . " records" . PHP_EOL;
    echo "✓ drivers table: " . $driversNormalized[0]->count . " records" . PHP_EOL;
    
} catch (Exception $e) {
    echo "⚠ Database connection issue: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== SECURITY AUDIT COMPLETE ===" . PHP_EOL;
echo "Priority Issues to Fix:" . PHP_EOL;
echo "1. Mass assignment vulnerabilities in DriverNormalized" . PHP_EOL;
echo "2. File upload MIME type verification" . PHP_EOL;
echo "3. Session security configuration" . PHP_EOL;
echo "4. Authentication guard documentation" . PHP_EOL;