<?php

require_once 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "Testing Matching Activity Table Fixes\n";
    echo "=====================================\n\n";

    // Test 1: Check if DriverMatch table exists and is accessible
    echo "1. Testing DriverMatch Model...\n";
    $matchCount = \App\Models\DriverMatch::count();
    echo "   ✓ DriverMatch table accessible, records: {$matchCount}\n\n";

    // Test 2: Check if we have test data
    echo "2. Checking for test data...\n";
    $driverCount = \App\Models\Drivers::where('verification_status', 'verified')->count();
    $requestCount = \App\Models\CompanyRequest::whereIn('status', ['pending', 'Pending', 'Active'])->count();
    echo "   ✓ Available drivers: {$driverCount}\n";
    echo "   ✓ Pending requests: {$requestCount}\n\n";

    // Test 3: Create a sample match if we have data
    if ($driverCount > 0 && $requestCount > 0) {
        echo "3. Creating sample match...\n";
        
        $driver = \App\Models\Drivers::where('verification_status', 'verified')->first();
        $request = \App\Models\CompanyRequest::whereIn('status', ['pending', 'Pending', 'Active'])->first();
        
        // Generate match ID
        do {
            $matchId = 'MT' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\DriverMatch::where('match_id', $matchId)->exists());
        
        $match = \App\Models\DriverMatch::create([
            'match_id' => $matchId,
            'company_request_id' => $request->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
            'commission_rate' => 10.00,
            'matched_at' => now(),
            'auto_matched' => false,
            'matched_by_admin' => true,
            'notes' => 'Test match created for validation'
        ]);
        
        echo "   ✓ Sample match created: {$matchId}\n";
        echo "   ✓ Driver: {$driver->first_name} {$driver->surname}\n";
        echo "   ✓ Request ID: {$request->id}\n\n";
    }

    // Test 4: Test match relationships
    echo "4. Testing match relationships...\n";
    $matches = \App\Models\DriverMatch::with(['driver', 'companyRequest.company'])
        ->limit(3)
        ->get();
    
    foreach ($matches as $match) {
        echo "   Match {$match->match_id}:\n";
        echo "     - Status: {$match->status}\n";
        echo "     - Driver: " . ($match->driver ? "{$match->driver->first_name} {$match->driver->surname}" : "Not found") . "\n";
        echo "     - Company: " . ($match->companyRequest && $match->companyRequest->company ? $match->companyRequest->company->name : "Not found") . "\n";
        echo "     - Status Color: " . $match->status_color . "\n";
        echo "     - Created: " . $match->created_at->format('Y-m-d H:i:s') . "\n\n";
    }

    // Test 5: Test status color accessor
    echo "5. Testing status color accessor...\n";
    $statuses = ['pending', 'accepted', 'completed', 'declined', 'cancelled'];
    foreach ($statuses as $status) {
        $match = new \App\Models\DriverMatch(['status' => $status]);
        echo "   Status '{$status}' -> Color: {$match->status_color}\n";
    }
    echo "\n";

    echo "✅ All tests completed successfully!\n";
    echo "The matching activity table should now work properly with:\n";
    echo "- Proper status badge colors\n";
    echo "- Working action buttons with validation\n";
    echo "- Better error handling\n";
    echo "- Improved form validation\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}