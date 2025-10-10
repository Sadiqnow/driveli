<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DRIVELINK DATABASE TABLE DUMP ===" . PHP_EOL . PHP_EOL;

try {
    echo "=== DRIVER TABLE STRUCTURE ===" . PHP_EOL;
    $driversColumns = DB::select('DESCRIBE drivers');
    printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 'Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
    echo str_repeat('-', 90) . PHP_EOL;
    foreach ($driversColumns as $column) {
        printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 
            $column->Field, 
            $column->Type, 
            $column->Null,
            $column->Key ?: '',
            $column->Default ?: 'NULL',
            $column->Extra ?: ''
        );
    }
    
    $driversCount = DB::table('drivers')->count();
    echo PHP_EOL . "Driver table record count: " . $driversCount . PHP_EOL . PHP_EOL;
    
    // Show sample data if exists
    if ($driversCount > 0) {
        echo "=== SAMPLE DRIVER DATA (First 3 records) ===" . PHP_EOL;
        $sampleDrivers = DB::table('drivers')->limit(3)->get();
        foreach ($sampleDrivers as $driver) {
            echo "ID: {$driver->id}, Name: {$driver->first_name} {$driver->surname}, Status: {$driver->status}, Verification: {$driver->verification_status}" . PHP_EOL;
        }
        echo PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error accessing drivers table: " . $e->getMessage() . PHP_EOL;
}

try {
    echo "=== DRIVER_NORMALIZED TABLE STRUCTURE ===" . PHP_EOL;
    $normalizedColumns = DB::select('DESCRIBE drivers');
    printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 'Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
    echo str_repeat('-', 90) . PHP_EOL;
    foreach ($normalizedColumns as $column) {
        printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 
            $column->Field, 
            $column->Type, 
            $column->Null,
            $column->Key ?: '',
            $column->Default ?: 'NULL',
            $column->Extra ?: ''
        );
    }
    
    $normalizedCount = DB::table('drivers')->count();
    echo PHP_EOL . "Driver_normalized table record count: " . $normalizedCount . PHP_EOL . PHP_EOL;
    
    // Show sample data if exists
    if ($normalizedCount > 0) {
        echo "=== SAMPLE DRIVER_NORMALIZED DATA (First 3 records) ===" . PHP_EOL;
        $sampleNormalized = DB::table('drivers')->limit(3)->get();
        foreach ($sampleNormalized as $normalized) {
            echo "ID: {$normalized->id}, Driver ID: {$normalized->driver_id}, Name: {$normalized->first_name} {$normalized->surname}" . PHP_EOL;
            if (isset($normalized->ocr_verification_status)) {
                echo "  OCR Status: {$normalized->ocr_verification_status}" . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error accessing drivers table: " . $e->getMessage() . PHP_EOL;
}

// Check for OCR related columns
try {
    echo "=== OCR COLUMNS CHECK ===" . PHP_EOL;
    $ocrColumns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'drivelink_db' AND COLUMN_NAME LIKE '%ocr%'");
    if (!empty($ocrColumns)) {
        echo "OCR-related columns found:" . PHP_EOL;
        foreach ($ocrColumns as $col) {
            echo "- " . $col->COLUMN_NAME . PHP_EOL;
        }
    } else {
        echo "No OCR-related columns found." . PHP_EOL;
    }
    echo PHP_EOL;
} catch (Exception $e) {
    echo "Error checking OCR columns: " . $e->getMessage() . PHP_EOL;
}

// Check migration status
try {
    echo "=== MIGRATION STATUS ===" . PHP_EOL;
    $migrations = DB::table('migrations')->orderBy('batch', 'desc')->get();
    echo "Recent migrations:" . PHP_EOL;
    foreach ($migrations->take(10) as $migration) {
        echo "- {$migration->migration} (Batch: {$migration->batch})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error checking migrations: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== DUMP COMPLETE ===" . PHP_EOL;