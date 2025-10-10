<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Driver\DriverKycController;

try {
    echo "Testing DriverKycController getLocalGovernments method...\n";
    
    // Create controller instance
    $controller = new DriverKycController();
    
    // Get first state ID
    $firstState = DB::table('states')->first();
    
    if (!$firstState) {
        echo "No states found in database. Creating test state...\n";
        $stateId = DB::table('states')->insertGetId(['name' => 'Test State', 'code' => 'TS']);
        DB::table('local_governments')->insert([
            ['name' => 'Test LGA 1', 'state_id' => $stateId],
            ['name' => 'Test LGA 2', 'state_id' => $stateId],
        ]);
        $firstState = (object)['id' => $stateId, 'name' => 'Test State'];
    }
    
    echo "Testing with state: {$firstState->name} (ID: {$firstState->id})\n";
    
    // Test the getLocalGovernments method
    $response = $controller->getLocalGovernments($firstState->id);
    $content = $response->getContent();
    
    echo "Response content: $content\n";
    
    $data = json_decode($content, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Successfully parsed JSON\n";
        echo "Number of LGAs returned: " . count($data) . "\n";
        
        if (count($data) > 0) {
            echo "Sample LGAs:\n";
            foreach (array_slice($data, 0, 3) as $lga) {
                echo "- {$lga['name']} (ID: {$lga['id']})\n";
            }
            echo "SUCCESS: LGA endpoint is working!\n";
        } else {
            echo "WARNING: No LGAs returned for this state\n";
        }
    } else {
        echo "ERROR: Failed to parse JSON response\n";
        echo "JSON error: " . json_last_error_msg() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}