<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('admin_users');
    echo "Admin Users Table Columns:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
