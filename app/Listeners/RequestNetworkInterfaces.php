<?php

namespace App\Listeners;

use App\Events\ReceivedNetworkInterfaces;
use App\Events\StartNewPdnsSynchronization;
use App\Facades\AzureArm\ResourceGraph;
use App\Traits\Token;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestNetworkInterfaces implements ShouldQueue
{
    use Token;

    public function viaConnection(): string
    {
        return config('app.env') === 'testing' ? 'sync' : 'redis';
    }

    public function handle(StartNewPdnsSynchronization $event)
    {
        $attributes = $event->getAttributes();

        $attributes['resources'] = ResourceGraph::withProvider($attributes['spoke'])
            ->type('microsoft.network/networkinterfaces')
            ->extend('name', 'tostring(properties.ipConfigurations)')
            ->project('name')
            ->get();

        event(new ReceivedNetworkInterfaces($attributes));
    }
}
