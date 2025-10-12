<?php

require_once 'vendor/autoload.php';

use App\Services\NotificationService;
use App\Models\Drivers;
use Illuminate\Database\Eloquent\Factories\Factory;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$notificationService = new NotificationService();

// Create a mock driver using factory
$driver = Drivers::factory()->make([
    'driver_id' => 'DRV001',
    'email' => 'test@example.com',
    'full_name' => 'Test Driver'
]);

// Test sendKycNotification
$result1 = $notificationService->sendKycNotification($driver);
echo "sendKycNotification result: " . json_encode($result1) . "\n";

// Test sendKycInfoRequestNotification
$result2 = $notificationService->sendKycInfoRequestNotification($driver, 'Please provide additional documents.');
echo "sendKycInfoRequestNotification result: " . json_encode($result2) . "\n";

echo "Notification tests completed.\n";
