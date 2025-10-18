<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = app('router')->getRoutes();
$targetRoute = null;

foreach ($routes as $route) {
    if ($route->getName() === 'admin.superadmin.drivers.index') {
        $targetRoute = $route;
        break;
    }
}

if ($targetRoute) {
    echo 'Route found: ' . $targetRoute->getName() . PHP_EOL;
    echo 'URI: ' . $targetRoute->uri() . PHP_EOL;
    echo 'Methods: ' . implode(', ', $targetRoute->methods()) . PHP_EOL;
    echo 'Middleware: ' . json_encode($targetRoute->middleware()) . PHP_EOL;
} else {
    echo 'Route not found' . PHP_EOL;
}
