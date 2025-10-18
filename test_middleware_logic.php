<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\AdminUser::find(1);
echo 'Testing middleware logic:' . PHP_EOL;
echo 'Role: ' . $user->role . PHP_EOL;
echo 'Role check: ' . ($user->role === 'Super Admin' ? 'true' : 'false') . PHP_EOL;
echo 'hasRole check: ' . ($user->hasRole('Super Admin') ? 'true' : 'false') . PHP_EOL;
echo 'Permission check: ' . ($user->hasPermission('manage_drivers') ? 'true' : 'false') . PHP_EOL;
echo 'Combined condition: ' . (($user->role !== 'Super Admin' && !$user->hasPermission('manage_drivers')) ? 'would block' : 'would allow') . PHP_EOL;
