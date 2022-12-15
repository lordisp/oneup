<?php

namespace App\Listeners;

use App\Events\FirewallReviewAvailableEvent;
use App\Events\ImportNewFirewallRequestsEvent;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportNewFirewallRequestsEventListener
{
    public function __construct()
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(ImportNewFirewallRequestsEvent $event): void
    {
        $user = $event->user;
        $event->batch
            ->then(function ($event) use ($user) {
                event(new FirewallReviewAvailableEvent($event, $user));
            })
            ->catch(function (Batch $batch, Throwable $exception) {
                Log::error(
                    'Failed to complete ' . $batch->failedJobs . ' Import Firewall-Request Jobs',
                    [
                        'error' => $exception->getMessage(),
                        'failedJobs' => $batch->failedJobIds
                    ]);
            })
            ->dispatch();
    }
}