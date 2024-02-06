<?php

namespace App\Listeners;

use App\Events\InterfacesReceived;
use App\Jobs\Pdns\ProcessPrivateDnsSyncJob;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueuePrivateDnsSync
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(InterfacesReceived $event): void
    {
        $this->dispatchToQueue($event->getProvider(), config('dnssync.queue_name'));
    }

    private function dispatchToQueue($provider, $queueName): void
    {
        ProcessPrivateDnsSyncJob::dispatch($provider)->onQueue($queueName);
    }
}
