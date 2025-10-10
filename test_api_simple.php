<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Create basic data first
    echo "Creating basic states and LGAs...\n";

    // Removed truncate statements to prevent deletion of LGAs table and banks table

    $lagos = DB::table('states')->insertGetId(['name' => 'Lagos', 'code' => 'LA']);
    $abuja = DB::table('states')->insertGetId(['name' => 'Abuja (FCT)', 'code' => 'FC']);

    // Insert LGAs for Lagos
    $lagosLgas = ['Ikeja', 'Lagos Island', 'Lagos Mainland', 'Surulere', 'Apapa'];
    foreach ($lagosLgas as $lga) {
        DB::table('local_governments')->insert(['name' => $lga, 'state_id' => $lagos]);
    }

    // Insert LGAs for Abuja
    $abujaLgas = ['Municipal Area Council', 'Gwagwalada', 'Kuje', 'Bwari', 'Abaji'];
    foreach ($abujaLgas as $lga) {
        DB::table('local_governments')->insert(['name' => $lga, 'state_id' => $abuja]);
    }

    echo "Data created successfully!\n";
    echo "States: " . DB::table('states')->count() . "\n";
    echo "LGAs: " . DB::table('local_governments')->count() . "\n\n";

    // Test API controller directly
    echo "Testing API...\n";
    $controller = new App\Http\Controllers\API\LocationController();

    // Test states endpoint
    $statesResponse = $controller->getStates();
    $statesContent = $statesResponse->getContent();
    echo "States API Response: $statesContent\n\n";

    // Test LGAs for Lagos (ID: 1)
    $request = new Illuminate\Http\Request();
    $lgasResponse = $controller->getLocalGovernments($request, $lagos);
    $lgasContent = $lgasResponse->getContent();
    echo "LGAs API Response for Lagos: $lgasContent\n\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
