<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\AdminUser::find(1);
echo 'Testing RBAC middleware logic for Super Admin:' . PHP_EOL;
echo 'Role: ' . $user->role . PHP_EOL;
echo 'hasRole(super_admin): ' . ($user->hasRole('super_admin') ? 'true' : 'false') . PHP_EOL;
echo 'role === Super Admin: ' . ($user->role === 'Super Admin' ? 'true' : 'false') . PHP_EOL;

// Test the bypass condition
$shouldBypass = $user->hasRole('super_admin') || $user->role === 'Super Admin';
echo 'Should bypass RBAC check: ' . ($shouldBypass ? 'true' : 'false') . PHP_EOL;
