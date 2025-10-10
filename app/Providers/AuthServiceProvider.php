<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define Gates for admin permissions
        $this->defineAdminGates();
    }

    /**
     * Define Gates for admin authorization
     */
    private function defineAdminGates()
    {
        // User management permissions - use admin guard
        Gate::define('manage_users', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_users') || $user->role === 'Super Admin';
        });

        Gate::define('view_users', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_users') || $user->hasPermission('view_users') || $user->role === 'Super Admin';
        });

        Gate::define('manage_drivers', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_drivers') || $user->role === 'Super Admin';
        });

        Gate::define('manage_companies', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_companies') || $user->role === 'Super Admin';
        });

        Gate::define('manage_requests', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_requests') || $user->role === 'Super Admin';
        });

        Gate::define('manage_matches', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_matches') || $user->role === 'Super Admin';
        });

        Gate::define('manage_commissions', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_commissions') || $user->role === 'Super Admin';
        });

        Gate::define('view_reports', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('view_reports') || $user->role === 'Super Admin';
        });

        Gate::define('manage_notifications', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_notifications') || $user->role === 'Super Admin';
        });

        Gate::define('manage_settings', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('manage_settings') || $user->role === 'Super Admin';
        });

        Gate::define('delete_records', function ($user = null) {
            $user = $user ?: auth('admin')->user();
            if (!$user) return false;
            return $user->hasPermission('delete_records') || $user->role === 'Super Admin';
        });

        // Super admin can do everything
        Gate::before(function ($user, $ability) {
            // Try admin guard first
            $user = $user ?: auth('admin')->user();
            if ($user && ($user->role === 'Super Admin' || $user->hasRole('Super Admin'))) {
                return true;
            }
        });
    }
}
