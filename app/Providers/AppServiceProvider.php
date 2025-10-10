<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SettingsService;
use App\Services\EncryptionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService();
        });

        // Bind EncryptionService for models and services that rely on container resolution
        $this->app->singleton(EncryptionService::class, function ($app) {
            return new EncryptionService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
