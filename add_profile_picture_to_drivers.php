<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Adding profile_picture column to drivers table...\n";

    // Check if column already exists
    $columns = DB::select("SHOW COLUMNS FROM drivers LIKE 'profile_picture'");
    if (count($columns) > 0) {
        echo "✅ profile_picture column already exists in drivers table!\n";
        return;
    }

    // Add the column
    DB::statement("ALTER TABLE drivers ADD COLUMN profile_picture VARCHAR(255) NULL");

    echo "✅ Successfully added profile_picture column to drivers table!\n";

    // Verify it was added
    $columns = DB::select("SHOW COLUMNS FROM drivers LIKE 'profile_picture'");
    if (count($columns) > 0) {
        echo "✅ Verification: profile_picture column is now present in drivers table.\n";
    } else {
        echo "❌ Verification failed: profile_picture column not found after addition.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
