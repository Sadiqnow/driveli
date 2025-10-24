<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\DriverKycCompleted;
use App\Listeners\NotifyAdminsOfKycCompletion;
use App\Events\RoleUpdated;
use App\Events\PermissionChanged;
use App\Events\UserRoleModified;
use App\Listeners\RoleUpdatedListener;
use App\Listeners\PermissionChangedListener;
use App\Listeners\UserRoleModifiedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        DriverKycCompleted::class => [
            NotifyAdminsOfKycCompletion::class,
        ],
        DriverVerified::class => [
            NotifyCompanyOnVerification::class,
        ],
        RoleUpdated::class => [
            RoleUpdatedListener::class,
        ],
        PermissionChanged::class => [
            PermissionChangedListener::class,
        ],
        UserRoleModified::class => [
            UserRoleModifiedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
