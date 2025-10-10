<?php

echo "PHP Upload Configuration Test\n";
echo "=============================\n\n";

// Check upload settings
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";

// Check storage directories
echo "\nStorage Directory Checks:\n";
echo "========================\n";

$storageDir = __DIR__ . '/storage/app/public';
echo "Storage directory: $storageDir\n";
echo "Storage directory exists: " . (is_dir($storageDir) ? 'Yes' : 'No') . "\n";
echo "Storage directory writable: " . (is_writable($storageDir) ? 'Yes' : 'No') . "\n";

$driverDocsDir = $storageDir . '/driver_documents';
echo "Driver documents directory: $driverDocsDir\n";
echo "Driver documents directory exists: " . (is_dir($driverDocsDir) ? 'Yes' : 'No') . "\n";
echo "Driver documents directory writable: " . (is_writable($driverDocsDir) ? 'Yes' : 'No') . "\n";

// Check if storage link exists
$publicStorageLink = __DIR__ . '/public/storage';
echo "Public storage link: $publicStorageLink\n";
echo "Public storage link exists: " . (file_exists($publicStorageLink) ? 'Yes' : 'No') . "\n";

if (file_exists($publicStorageLink)) {
    echo "Public storage link target: " . readlink($publicStorageLink) . "\n";
}

echo "\nPHP Version: " . phpversion() . "\n";
echo "Test completed.\n";