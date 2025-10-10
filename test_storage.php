<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Storage Disks:\n";

$disks = ['documents', 'secure_documents', 'temp', 'ocr_results'];

foreach ($disks as $disk) {
    try {
        Storage::disk($disk)->put('test.txt', 'test content');
        Storage::disk($disk)->delete('test.txt');
        echo "✓ $disk disk: OK\n";
    } catch (Exception $e) {
        echo "✗ $disk disk: ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\nChecking directory permissions:\n";
foreach ($disks as $disk) {
    $path = storage_path("app/$disk");
    if (is_dir($path)) {
        echo "✓ $disk directory exists at: $path\n";
        if (is_writable($path)) {
            echo "  ✓ Directory is writable\n";
        } else {
            echo "  ✗ Directory is not writable\n";
        }
    } else {
        echo "✗ $disk directory missing at: $path\n";
    }
}