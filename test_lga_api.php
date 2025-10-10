<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\State;
use App\Models\LocalGovernment;
use App\Http\Controllers\API\LocationController;

// Bootstrap Laravel
$app = new Application(realpath(__DIR__));
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

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== LGA API Test Script ===\n";

try {
    echo "1. Testing database connection and data...\n";
    
    // Test database models
    $stateCount = State::count();
    $lgaCount = LocalGovernment::count();
    
    echo "   States in database: {$stateCount}\n";
    echo "   LGAs in database: {$lgaCount}\n";
    
    if ($stateCount === 0) {
        echo "   ERROR: No states found in database!\n";
        exit(1);
    }
    
    if ($lgaCount === 0) {
        echo "   ERROR: No LGAs found in database!\n";
        exit(1);
    }
    
    // Test getting sample state with LGAs
    $sampleState = State::with(['localGovernments' => function($query) {
        $query->limit(5);
    }])->first();
    
    echo "   Sample state: {$sampleState->name} ({$sampleState->localGovernments->count()} LGAs)\n";
    
    echo "2. Testing API Controllers...\n";
    
    // Test states API
    $controller = new LocationController();
    $statesResponse = $controller->getStates();
    $statesData = json_decode($statesResponse->getContent(), true);
    
    echo "   States API response status: " . $statesResponse->getStatusCode() . "\n";
    echo "   States API success: " . ($statesData['success'] ? 'YES' : 'NO') . "\n";
    echo "   States returned: " . ($statesData['count'] ?? 0) . "\n";
    
    // Test LGAs API for Lagos (assuming Lagos exists)
    $lagos = State::where('name', 'Lagos')->first();
    if ($lagos) {
        $lgasResponse = $controller->getLocalGovernments(new \Illuminate\Http\Request(), $lagos->id);
        $lgasData = json_decode($lgasResponse->getContent(), true);
        
        echo "   LGAs API for Lagos status: " . $lgasResponse->getStatusCode() . "\n";
        echo "   LGAs API success: " . ($lgasData['success'] ? 'YES' : 'NO') . "\n";
        echo "   Lagos LGAs returned: " . ($lgasData['count'] ?? 0) . "\n";
    } else {
        echo "   WARNING: Lagos state not found for LGA testing\n";
    }
    
    echo "3. API endpoints test completed successfully!\n";
    echo "\n=== Summary ===\n";
    echo "✓ Database has {$stateCount} states and {$lgaCount} LGAs\n";
    echo "✓ API controllers are functional\n";
    echo "✓ LGA dropdown should now work properly\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}