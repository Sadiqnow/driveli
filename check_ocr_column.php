<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Checking ocr_verification_status column in drivers table...\n";

    // Check if column exists
    $columns = DB::select("SHOW COLUMNS FROM drivers LIKE 'ocr_verification_status'");
    if (count($columns) > 0) {
        echo "✅ ocr_verification_status column EXISTS in drivers table\n";
        echo "Type: " . $columns[0]->Type . "\n";
        echo "Null: " . $columns[0]->Null . "\n";
        echo "Default: " . ($columns[0]->Default ?? 'NULL') . "\n";
    } else {
        echo "❌ ocr_verification_status column MISSING in drivers table\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
