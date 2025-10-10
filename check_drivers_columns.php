<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = Schema::getColumnListing('drivers');

echo "Columns in drivers table:\n";
foreach($columns as $column) {
    echo $column . "\n";
}

echo "\nChecking for new employment columns:\n";
$newColumns = ['is_working', 'previous_workplace', 'previous_work_id_record', 'reason_stopped_working'];
foreach($newColumns as $col) {
    $exists = Schema::hasColumn('drivers', $col);
    echo "$col exists: " . ($exists ? 'YES' : 'NO') . "\n";
}
