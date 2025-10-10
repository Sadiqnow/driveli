<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    foreach ((array)$t as $v) {
        echo $v . PHP_EOL;
    }
}
