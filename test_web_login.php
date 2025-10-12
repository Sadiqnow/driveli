<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthenticationService;

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

    // Create a request object
    $request = new Request();
    $request->merge([
        'email' => $email,
        'password' => $password,
        'remember' => false
    ]);

    // Test AuthenticationService
    $authService = app(AuthenticationService::class);
    $result = $authService->authenticateAdmin(['email' => $email, 'password' => $password], false);

    echo "AuthenticationService result: " . ($result ? 'true' : 'false') . "\n";

    if ($result) {
        echo "✅ Authentication successful.\n";
        echo "Logged in user: " . Auth::guard('admin')->user()->email . "\n";
    } else {
        echo "❌ Authentication failed.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
