<?php

namespace App\Listeners;

use App\Events\FirewallReviewAvailableEvent;
use App\Notifications\FirewallRequestsImportedNotification;
use Illuminate\Support\Facades\Log;

class NotifyFirewallImportDispatcherListener
{
    public function __construct()
    {
    }

    public function handle(FirewallReviewAvailableEvent $event): void
    {
        try {
            $event->user->notify(new FirewallRequestsImportedNotification($event->event->toArray()));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}