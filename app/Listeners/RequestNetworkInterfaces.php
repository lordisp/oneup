<?php

namespace App\Listeners;

use App\Events\StartNewPdnsSynchronization;
use App\Jobs\RequestNetworkInterfacesJob;
use App\Traits\Token;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestNetworkInterfaces implements ShouldQueue, ShouldBeUnique
{
    use Token;

    public function viaConnection(): string
    {
        return config('app.env') === 'testing' ? 'sync' : 'redis';
    }

    public function handle(StartNewPdnsSynchronization $event)
    {
        RequestNetworkInterfacesJob::dispatch($event);
    }
}
