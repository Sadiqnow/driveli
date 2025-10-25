<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AdminUser;

$user = AdminUser::where('email', 'admin@drivelink.com')->first();
if ($user) {
    echo 'User: ' . $user->name . ' (' . $user->email . ')' . PHP_EOL;
    echo 'Role: ' . $user->role . PHP_EOL;
    echo 'Permissions: ' . json_encode($user->permissions) . PHP_EOL;
    echo 'Has Super Admin role: ' . ($user->hasRole('super_admin') ? 'YES' : 'NO') . PHP_EOL;
    echo 'Has manage_superadmin permission: ' . ($user->hasPermission('manage_superadmin') ? 'YES' : 'NO') . PHP_EOL;
    echo 'Has manage_drivers permission: ' . ($user->hasPermission('manage_drivers') ? 'YES' : 'NO') . PHP_EOL;
    echo 'Is Super Admin: ' . ($user->isSuperAdmin() ? 'YES' : 'NO') . PHP_EOL;

    // Check roles relationship
    echo PHP_EOL . 'Roles via relationship:' . PHP_EOL;
    try {
        $roles = $user->roles;
        foreach ($roles as $role) {
            echo '- ' . $role->name . ' (Level: ' . $role->level . ')' . PHP_EOL;
        }
    } catch (Exception $e) {
        echo 'Error checking roles: ' . $e->getMessage() . PHP_EOL;
    }

} else {
    echo 'User not found' . PHP_EOL;
}
