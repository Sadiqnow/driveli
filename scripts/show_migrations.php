<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = DB::table('migrations')->orderBy('batch','asc')->orderBy('migration','asc')->get();
foreach ($rows as $r) {
    echo $r->id . '\t' . $r->migration . '\t' . $r->batch . PHP_EOL;
}
