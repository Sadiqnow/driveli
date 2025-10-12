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
    $email = 'superadmin@drivelink.com';
    $password = 'Blackzonex@1986..';

    $admin = AdminUser::where('email', $email)->first();

    if ($admin) {
        echo "Admin found.\n";
        echo "Status: {$admin->status}\n";
        echo "Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";

        if (Hash::check($password, $admin->password)) {
            echo "✅ Password matches.\n";
        } else {
            echo "❌ Password does not match.\n";
            echo "Updating password...\n";
            $admin->password = Hash::make($password);
            $admin->save();
            echo "Password updated.\n";
        }
    } else {
        echo "Admin not found.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
