<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AdminUser;

if (!AdminUser::where('email', 'admin@drivelink.com')->exists()) {
    AdminUser::create([
        'name' => 'Test Admin',
        'email' => 'admin@drivelink.com',
        'password' => bcrypt('password123'),
        'role' => 'Super Admin'
    ]);
    echo "Admin user created\n";
} else {
    echo "Admin user already exists\n";
}
