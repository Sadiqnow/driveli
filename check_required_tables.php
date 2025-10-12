<?php

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Schema;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$requiredTables = [
    'users',
    'roles',
    'permissions',
    'role_user',
    'permission_role',
    'driver_profiles',
    'companies',
    'driver_company_relations',
    'admin_actions',
    'verifications',
    'activity_logs',
    'deactivation_requests',
    'otp_notifications'
];

echo "Checking required tables:\n";
foreach ($requiredTables as $table) {
    $exists = Schema::hasTable($table);
    echo "$table: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}
