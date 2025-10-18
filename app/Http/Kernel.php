<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\SecurityHeadersMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\DatabaseQueryLogger::class,
            \App\Http\Middleware\PerformanceMonitoringMiddleware::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        
        // Custom DriveLink middleware
        'rate_limit.auth' => \App\Http\Middleware\RateLimitAuth::class,
        'validate.api.key' => \App\Http\Middleware\ValidateApiKey::class,
        'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
        'enhanced.rate.limit' => \App\Http\Middleware\EnhancedRateLimitMiddleware::class,
        'secure.api' => \App\Http\Middleware\SecureApiMiddleware::class,
        'performance.monitor' => \App\Http\Middleware\PerformanceMonitoringMiddleware::class,
        
        // RBAC middleware
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        'check.permission' => \App\Http\Middleware\CheckPermission::class,
        'rbac' => \App\Http\Middleware\RBACMiddleware::class,
    'ability' => \App\Http\Middleware\AbilityMiddleware::class,
        
        // KYC middleware
        'kyc.step' => \App\Http\Middleware\EnsureKycStepOrder::class,
        'kyc.rate.limit' => \App\Http\Middleware\KycRateLimit::class,

        // Super Admin middleware
        'superadmin.driver.access' => \App\Http\Middleware\SuperAdminDriverAccess::class,
        'SuperAdminDriverAccess' => \App\Http\Middleware\SuperAdminDriverAccess::class,
        'register.superadmin.middleware' => \App\Http\Middleware\RegisterSuperAdminMiddleware::class,
    ];
}
