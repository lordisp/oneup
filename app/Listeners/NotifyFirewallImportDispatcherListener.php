<?php

namespace App\Listeners;

use App\Events\FirewallReviewAvailableEvent;
use App\Events\NotifyFirewallImportCompletedEvent;

class NotifyFirewallImportDispatcherListener
{
    public function handle(FirewallReviewAvailableEvent $event): void
    {
        event(new NotifyFirewallImportCompletedEvent($event));
    }
}
