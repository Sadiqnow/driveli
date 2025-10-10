<?php

// Lightweight schema diff script. Run from project root: php scripts/schema_diff.php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Scanning models in app/Models...\n\n";

$modelsPath = __DIR__ . '/../app/Models';
$files = glob($modelsPath . '/*.php');

$missing = [];

foreach ($files as $file) {
    $class = '\App\\Models\\' . basename($file, '.php');

    if (!class_exists($class)) {
        continue;
    }

    try {
        $model = new $class();
    } catch (Throwable $e) {
        // skip models that require constructor params
        continue;
    }

    if (!method_exists($model, 'getTable')) {
        continue;
    }

    $table = $model->getTable();

    // Collect candidate fields from fillable and casts
    $fillable = property_exists($model, 'fillable') ? $model->getFillable() : [];
    $casts = property_exists($model, 'casts') ? array_keys($model->getCasts()) : [];

    // Merge with attributes and visible/hidden if available
    $attributes = array_merge($fillable, $casts);
    $attributes = array_unique(array_filter($attributes));

    if (empty($attributes)) {
        continue;
    }

    foreach ($attributes as $attr) {
        if (!$attr) continue;
        if (!Schema::hasTable($table)) {
            $missing[$table]['table_missing'] = true;
            continue 2; // skip other attrs for this model
        }

        if (!Schema::hasColumn($table, $attr)) {
            $missing[$table]['columns'][] = $attr;
        }
    }
}

if (empty($missing)) {
    echo "No missing columns found for model fillable/casts.\n";
    exit(0);
}

foreach ($missing as $table => $info) {
    if (!empty($info['table_missing'])) {
        echo "Table not found: {$table}\n";
        continue;
    }

    $cols = array_unique($info['columns']);
    echo "Table: {$table}\n";
    echo "  Missing columns: " . implode(', ', $cols) . "\n\n";
}

echo "Done.\n";
