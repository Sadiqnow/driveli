<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Notification System\n";
echo "===========================\n";

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "✓ Laravel bootstrapped successfully\n";
    
    // Test email configuration
    echo "\nEmail Configuration:\n";
    echo "- Default mailer: " . config('mail.default', 'not set') . "\n";
    echo "- From address: " . config('mail.from.address', 'not set') . "\n";
    echo "- From name: " . config('mail.from.name', 'not set') . "\n";
    
    // Test SendGrid configuration
    $sendgridKey = env('SENDGRID_API_KEY');
    if ($sendgridKey && $sendgridKey !== 'your_sendgrid_api_key_here') {
        echo "- SendGrid: API key configured\n";
    } else {
        echo "- SendGrid: Using placeholder key (will log instead of sending)\n";
    }
    
    // Test if we can create notification service
    $notificationService = new \App\Services\NotificationService();
    echo "✓ NotificationService created\n";
    
    // Create a test driver or use existing one
    $testDriver = \App\Models\DriverNormalized::first();
    
    if (!$testDriver) {
        echo "\nCreating test driver...\n";
        $testDriver = \App\Models\DriverNormalized::create([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'email' => 'test@example.com',
            'phone' => '+2348012345678',
            'password' => bcrypt('password123'),
            'status' => 'pending',
            'verification_status' => 'pending'
        ]);
        echo "✓ Test driver created: {$testDriver->driver_id}\n";
    } else {
        echo "\nUsing existing driver: {$testDriver->first_name} {$testDriver->last_name}\n";
    }
    
    // Test sending welcome notification
    echo "\nTesting welcome notification...\n";
    $result = $notificationService->sendDriverWelcomeNotification($testDriver);
    
    if ($result['success']) {
        echo "✓ Welcome notification sent successfully\n";
    } else {
        echo "✗ Welcome notification failed: {$result['message']}\n";
    }
    
    // Test sending verification notification
    echo "\nTesting verification notification...\n";
    $result = $notificationService->sendVerificationNotification($testDriver, 'verified', 'All documents approved');
    
    if ($result['success']) {
        echo "✓ Verification notification sent successfully\n";
    } else {
        echo "✗ Verification notification failed: {$result['message']}\n";
    }
    
    echo "\nTest completed! Check storage/logs/laravel.log for detailed notification logs.\n";
    
} catch (Exception $e) {
    echo "\n❌ Error occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}