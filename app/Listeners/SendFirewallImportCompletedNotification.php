<?php

namespace App\Listeners;

use App\Events\NotifyFirewallImportCompletedEvent;
use App\Notifications\FirewallRequestsImportedNotification;
use Illuminate\Support\Facades\DB;

class SendFirewallImportCompletedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotifyFirewallImportCompletedEvent $event): void
    {
        if ($this->hasPendingJobs()) {

            sleep(5);

            event(new NotifyFirewallImportCompletedEvent($event));

            return;
        }

        $event->event->user->notify(new FirewallRequestsImportedNotification($event->event->event->toArray()));
    }

    private function hasPendingJobs(): bool
    {
        return DB::table('job_batches')
            ->where('name', '=', 'import-user-with-business-service')
            ->where('pending_jobs', '!=', 0)
            ->count() > 0;
    }
}
