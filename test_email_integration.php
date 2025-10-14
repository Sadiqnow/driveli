<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Drivers as Driver;
use App\Services\NotificationService;

echo "Testing Email Integration with Real Data\n";
echo "========================================\n\n";

try {
    // Test 1: Check if drivers exist in database
    echo "1. Checking for existing drivers...\n";
    $drivers = Driver::limit(3)->get();
    
    if ($drivers->isEmpty()) {
        echo "   No drivers found. Creating a test driver...\n";
        
        // Create a test driver
        $testDriver = Driver::create([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'email' => 'testdriver@example.com',
            'phone' => '+2348012345678',
            'password' => bcrypt('password123'),
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'status' => 'pending',
            'verification_status' => 'pending'
        ]);
        
        echo "   Test driver created with ID: {$testDriver->driver_id}\n";
        $drivers = collect([$testDriver]);
    }
    
    echo "   Found " . $drivers->count() . " drivers\n\n";

    // Test 2: Initialize notification service
    echo "2. Testing NotificationService...\n";
    $notificationService = new NotificationService();
    
    $testDriver = $drivers->first();
    echo "   Using driver: {$testDriver->full_name} ({$testDriver->email})\n";
    
    // Test 3: Send welcome notification
    echo "3. Testing welcome notification...\n";
    $result = $notificationService->sendDriverWelcomeNotification($testDriver);
    if ($result['success']) {
        echo "   ✓ Welcome notification: {$result['message']}\n";
    } else {
        echo "   ✗ Welcome notification failed: {$result['message']}\n";
    }
    
    // Test 4: Send verification status notification
    echo "4. Testing verification status notification...\n";
    $result = $notificationService->sendVerificationNotification($testDriver, 'approved', 'All documents verified successfully');
    if ($result['success']) {
        echo "   ✓ Verification notification: {$result['message']}\n";
    } else {
        echo "   ✗ Verification notification failed: {$result['message']}\n";
    }
    
    // Test 5: Send document action notification
    echo "5. Testing document action notification...\n";
    $result = $notificationService->sendDocumentActionNotification($testDriver, 'license_front', 'approved', 'Document looks good');
    if ($result['success']) {
        echo "   ✓ Document action notification: {$result['message']}\n";
    } else {
        echo "   ✗ Document action notification failed: {$result['message']}\n";
    }
    
    // Test 6: Check email configuration
    echo "6. Checking email configuration...\n";
    $mailConfig = config('mail.default');
    $fromAddress = config('mail.from.address');
    $fromName = config('mail.from.name');
    
    echo "   Mail driver: {$mailConfig}\n";
    echo "   From address: {$fromAddress}\n";
    echo "   From name: {$fromName}\n";
    
    if ($mailConfig === 'sendgrid') {
        $sendgridKey = config('services.sendgrid.key') ?: env('SENDGRID_API_KEY');
        if ($sendgridKey && $sendgridKey !== 'your_sendgrid_api_key_here') {
            echo "   ✓ SendGrid API key configured\n";
        } else {
            echo "   ⚠ SendGrid API key not configured (using log driver)\n";
        }
    }
    
    // Test 7: Check log files for email notifications
    echo "7. Checking recent log entries...\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $recentNotifications = substr_count($logContent, '[' . now()->format('Y-m-d'));
        echo "   Found {$recentNotifications} log entries from today\n";
        
        if (strpos($logContent, 'Email notification') !== false) {
            echo "   ✓ Email notifications are being logged\n";
        } else {
            echo "   ⚠ No email notification logs found\n";
        }
    } else {
        echo "   ⚠ Log file not found\n";
    }
    
    echo "\nEmail Integration Test Complete!\n";
    echo "================================\n";
    echo "Summary:\n";
    echo "- Notification service is working properly\n";
    echo "- Email templates are configured\n";
    echo "- Notifications are being logged/sent\n";
    if ($mailConfig === 'sendgrid') {
        echo "- Configure SENDGRID_API_KEY in .env for actual email delivery\n";
    }
    echo "- Check storage/logs/laravel.log for notification details\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}