<?php

namespace App\Providers;

use App\Events\InterfacesReceived;
use App\Events\VmStateChangeEvent;
use App\Listeners\QueuePrivateDnsSync;
use App\Listeners\SessionExpiredListener;
use App\Listeners\VmStateChangeProcessListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\FirewallReviewAvailableEvent::class => [
            \App\Listeners\NotifyFirewallImportDispatcherListener::class,
            \App\Listeners\CleanUpFirewallRulesListener::class,
        ],
        \App\Events\ImportNewFirewallRequestsEvent::class => [
            \App\Listeners\ImportNewFirewallRequestsEventListener::class,
        ],

        \App\Events\NotifyFirewallImportCompletedEvent::class => [
            \App\Listeners\SendFirewallImportCompletedNotification::class,
        ],

        'session.expire' => [
            SessionExpiredListener::class,
        ],
        VmStateChangeEvent::class => [
            VmStateChangeProcessListener::class,
        ],

        InterfacesReceived::class => [
            QueuePrivateDnsSync::class,
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
