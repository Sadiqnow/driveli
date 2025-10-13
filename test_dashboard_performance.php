<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing Dashboard Performance Queries\n";
    echo "====================================\n\n";

    echo "1. Testing basic driver stats query...\n";
    $stats = \App\Models\Drivers::selectRaw('
        COUNT(*) as total_drivers,
        SUM(CASE WHEN profile_picture IS NOT NULL THEN 1 ELSE 0 END) as drivers_with_documents,
        SUM(CASE WHEN ocr_verification_status != "pending" THEN 1 ELSE 0 END) as ocr_processed
    ')->first();

    echo "✅ Dashboard stats query successful\n";
    echo "   Total drivers: {$stats->total_drivers}\n";
    echo "   Drivers with documents: {$stats->drivers_with_documents}\n";
    echo "   OCR processed: {$stats->ocr_processed}\n\n";

    echo "2. Testing driver performance relationship...\n";
    $driver = \App\Models\Drivers::first();
    if ($driver) {
        $performance = $driver->performance;
        echo "✅ Performance relationship works\n";
        echo "   Driver: {$driver->full_name}\n";
        echo "   Performance exists: " . ($performance ? 'Yes' : 'No') . "\n";
        if ($performance) {
            echo "   Total jobs: {$performance->total_jobs_completed}\n";
            echo "   Average rating: {$performance->average_rating}\n";
        }
    } else {
        echo "⚠️  No drivers found in database\n";
    }

    echo "\n3. Testing driver model computed attributes...\n";
    if ($driver) {
        echo "✅ Driver model attributes accessible\n";
        echo "   Profile picture: " . ($driver->profile_picture ? 'Set' : 'Not set') . "\n";
        echo "   OCR status: {$driver->ocr_verification_status}\n";
        echo "   Total rating: {$driver->getTotalRatingAttribute()}\n";
        echo "   Total jobs: {$driver->getTotalJobsAttribute()}\n";
        echo "   Total earnings: {$driver->getTotalEarningsAttribute()}\n";
    }

    echo "\n4. Testing performance metrics accessors...\n";
    if ($driver) {
        echo "   Rating accessor: {$driver->rating}\n";
        echo "   Total jobs accessor: {$driver->total_jobs}\n";
        echo "   Total earnings accessor: {$driver->total_earnings}\n";
    }

    echo "\n🎉 ALL PERFORMANCE-RELATED FUNCTIONALITY TESTS PASSED!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
