<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\AdminUser::find(1);
echo 'User: ' . $user->name . PHP_EOL;
echo 'Role: ' . $user->role . PHP_EOL;
echo 'hasRole Super Admin: ' . ($user->hasRole('Super Admin') ? 'true' : 'false') . PHP_EOL;
echo 'hasRole super_admin: ' . ($user->hasRole('super_admin') ? 'true' : 'false') . PHP_EOL;
echo 'hasPermission manage_drivers: ' . ($user->hasPermission('manage_drivers') ? 'true' : 'false') . PHP_EOL;
echo 'Permissions: ' . json_encode($user->permissions) . PHP_EOL;
