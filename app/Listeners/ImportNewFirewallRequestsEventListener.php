<?php

namespace App\Listeners;

use App\Events\FirewallReviewAvailableEvent;
use App\Events\ImportNewFirewallRequestsEvent;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportNewFirewallRequestsEventListener
{
    public const BATCH_NAME = 'import-firewall-reviews';

    /**
     * @throws Throwable
     */
    public function handle(ImportNewFirewallRequestsEvent $event): void
    {
        $this->dispatchBatch($event);
    }

    /**
     * @throws Throwable
     */
    protected function dispatchBatch(ImportNewFirewallRequestsEvent $event): void
    {
        $user = $event->user;
        $event->batch
            ->catch($this->logFailureCallback())
            ->onQueue(config('firewallmanagement.queues.import', 'default'))
            ->name(self::BATCH_NAME)
            ->allowFailures()
            ->then(function ($batch) use ($user) {
                event(new FirewallReviewAvailableEvent($batch, $user));
            })
            ->dispatch();
    }

    private function logFailureCallback(): \Closure
    {
        return function (Batch $batch, Throwable $exception) {
            Log::error(
                'Failed to complete '.$batch->failedJobs.' Import Firewall-Request Jobs',
                [
                    'error' => $exception->getMessage(),
                    'failedJobs' => $batch->failedJobIds,
                    'trace' => $exception->getTrace(),
                    'event' => 'ImportNewFirewallRequestsEvent',
                ]);
        };
    }
}
