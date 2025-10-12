<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = new Application(__DIR__);
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking Admin Users...\n\n";

    $admins = AdminUser::all();
    if ($admins->isEmpty()) {
        echo "No admin users found.\n";
    } else {
        foreach ($admins as $admin) {
            echo "ID: {$admin->id}\n";
            echo "Name: {$admin->name}\n";
            echo "Email: {$admin->email}\n";
            echo "Role: {$admin->role}\n";
            echo "Status: {$admin->status}\n";
            echo "Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
            echo "Password Hash: " . substr($admin->password, 0, 20) . "...\n";
            echo "Created At: {$admin->created_at}\n";
            echo "---\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
