<?php
// Simple CSRF token test
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Session;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Testing CSRF token generation...\n";

// Start session
session_start();

// Generate CSRF token
$token = csrf_token();
echo "Generated CSRF token: " . $token . "\n";

// Check if session is working
echo "Session ID: " . session_id() . "\n";
echo "Session path: " . session_save_path() . "\n";
echo "Session name: " . session_name() . "\n";

// Test if session data can be written
$_SESSION['test_data'] = 'test_value_' . time();
echo "Test session data written: " . $_SESSION['test_data'] . "\n";

echo "CSRF/Session test completed.\n";
?>