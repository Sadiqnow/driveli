<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\AdminUser;

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
    $admin = AdminUser::withTrashed()->where('email', 'superadmin@drivelink.com')->first();

    if ($admin) {
        echo "Deleted At: " . ($admin->deleted_at ? $admin->deleted_at : 'null') . "\n";
        if ($admin->deleted_at) {
            echo "Admin is soft deleted. Restoring...\n";
            $admin->restore();
            echo "Restored.\n";
        } else {
            echo "Not deleted.\n";
        }
    } else {
        echo "Admin not found.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
