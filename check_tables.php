<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = DB::select('SHOW TABLES');

echo "Tables in database:\n";
foreach($tables as $table) {
    $tableName = 'Tables_in_' . env('DB_DATABASE', 'drivelink_db');
    echo $table->$tableName . "\n";
}

echo "\nChecking for drivers table:\n";
$exists = Schema::hasTable('drivers');
echo "drivers exists: " . ($exists ? 'YES' : 'NO') . "\n";
