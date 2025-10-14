<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\DriverRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\AdminUserRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\VerificationRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\LocationRepository;
use App\Repositories\PerformanceRepository;
use App\Repositories\BankingDetailRepository;
use App\Repositories\NextOfKinRepository;
use App\Repositories\EmploymentHistoryRepository;
use App\Repositories\PreferenceRepository;
use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;

/**
 * Repository Service Provider
 * 
 * Registers all repository bindings in the service container.
 * This allows for dependency injection of repositories throughout the application.
 * 
 * @package App\Providers
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind repositories to the service container
        $this->app->singleton(DriverRepository::class, function ($app) {
            return new DriverRepository();
        });

        $this->app->singleton(CompanyRepository::class, function ($app) {
            return new CompanyRepository();
        });

        $this->app->singleton(AdminUserRepository::class, function ($app) {
            return new AdminUserRepository();
        });

        // Document and verification repositories
        $this->app->singleton(DocumentRepository::class, function ($app) {
            return new DocumentRepository();
        });

        $this->app->singleton(VerificationRepository::class, function ($app) {
            return new VerificationRepository();
        });

        $this->app->singleton(NotificationRepository::class, function ($app) {
            return new NotificationRepository();
        });

        // Driver-related repositories
        $this->app->singleton(LocationRepository::class, function ($app) {
            return new LocationRepository();
        });

        $this->app->singleton(PerformanceRepository::class, function ($app) {
            return new PerformanceRepository();
        });

        $this->app->singleton(BankingDetailRepository::class, function ($app) {
            return new BankingDetailRepository();
        });

        $this->app->singleton(NextOfKinRepository::class, function ($app) {
            return new NextOfKinRepository();
        });

        $this->app->singleton(EmploymentHistoryRepository::class, function ($app) {
            return new EmploymentHistoryRepository();
        });

        $this->app->singleton(PreferenceRepository::class, function ($app) {
            return new PreferenceRepository();
        });

        // Authorization repositories
        $this->app->singleton(RoleRepository::class, function ($app) {
            return new RoleRepository();
        });

        $this->app->singleton(PermissionRepository::class, function ($app) {
            return new PermissionRepository();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
