<?php

namespace App\Listeners;

use App\Events\FirewallReviewAvailableEvent;
use App\Notifications\FirewallRequestsImportedNotification;

class NotifyFirewallImportDispatcherListener
{
    public function handle(FirewallReviewAvailableEvent $event): void
    {
        $event->user->notify(new FirewallRequestsImportedNotification($event->event->toArray()));
    }
}