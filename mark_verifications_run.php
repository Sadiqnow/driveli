<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::table('migrations')->insert([
    'migration' => '2025_10_12_121719_create_verifications_table',
    'batch' => 3
]);

echo "Migration marked as run.\n";
