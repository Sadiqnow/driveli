<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\AdminUser;
use App\Models\Role;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    // Check if superadmin already exists
    $existing = AdminUser::where('email', 'superadmin@drivelink.com')->first();
    if ($existing) {
        echo "SuperAdmin already exists!\n";
        exit(0);
    }

    // Get super_admin role
    $role = Role::where('name', 'super_admin')->first();
    if (!$role) {
        echo "Super admin role not found! Please run role seeder first.\n";
        exit(1);
    }

    // Create superadmin user
    $user = new AdminUser();
    $user->name = 'Super Admin';
    $user->email = 'superadmin@drivelink.com';
    $user->password = bcrypt('password');
    $user->role = 'super_admin'; // Use the role string field
    $user->save();

    echo "SuperAdmin created successfully!\n";
    echo "Email: superadmin@drivelink.com\n";
    echo "Password: password\n";

} catch (Exception $e) {
    echo "Error creating SuperAdmin: " . $e->getMessage() . "\n";
    exit(1);
}
