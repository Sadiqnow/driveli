<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Services\VerificationStatusService;

// Bootstrap Laravel
$app = new Application(__DIR__);
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing VerificationStatusService::getDriverVerificationDetails...\n\n";

    $service = app(VerificationStatusService::class);

    $driverId = 120; // From check_drivers_status.php

    echo "Calling getDriverVerificationDetails for driver ID: {$driverId}\n";

    $result = $service->getDriverVerificationDetails($driverId);

    echo "Result:\n";
    echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";

    if ($result['success']) {
        echo "Driver: " . ($result['driver'] ? $result['driver']->first_name . ' ' . $result['driver']->last_name : 'null') . "\n";
        echo "Workflow: " . ($result['workflow'] ? 'present' : 'null') . "\n";
        echo "Verifications count: " . $result['verifications']->count() . "\n";
        echo "OCR Results count: " . $result['ocr_results']->count() . "\n";
        echo "API Logs count: " . $result['api_logs']->count() . "\n";
        echo "Referee Verifications count: " . $result['referee_verifications']->count() . "\n";
        echo "Verification Summary: " . ($result['verification_summary'] ? 'present' : 'null') . "\n";
    } else {
        echo "Error: " . $result['error'] . "\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
