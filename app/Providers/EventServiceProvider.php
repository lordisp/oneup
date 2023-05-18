<?php

namespace App\Providers;

use App\Events\FirewallReviewAvailableEvent;
use App\Events\ImportNewFirewallRequestsEvent;
use App\Events\ReceivedNetworkInterfaces;
use App\Events\StartNewPdnsSynchronization;
use App\Listeners\CleanUpFirewallRulesListener;
use App\Listeners\ImportNewFirewallRequestsEventListener;
use App\Listeners\NotifyFirewallImportDispatcherListener;
use App\Listeners\RequestNetworkInterfaces;
use App\Listeners\SessionExpiredListener;
use App\Listeners\SynchronizePdnsZones;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FirewallReviewAvailableEvent::class => [
            NotifyFirewallImportDispatcherListener::class,
            CleanUpFirewallRulesListener::class,
        ],
        ImportNewFirewallRequestsEvent::class => [
            ImportNewFirewallRequestsEventListener::class,
        ],
        'session.expire' => [
            SessionExpiredListener::class
        ],
        StartNewPdnsSynchronization::class => [
            RequestNetworkInterfaces::class,
        ],
        ReceivedNetworkInterfaces::class => [
            SynchronizePdnsZones::class
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
