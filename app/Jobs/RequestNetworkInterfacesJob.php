<?php

namespace App\Jobs;

use App\Events\ReceivedNetworkInterfaces;
use App\Events\StartNewPdnsSynchronization;
use App\Facades\AzureArm\ResourceGraph;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestNetworkInterfacesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId():string
    {
        return $this->event->getAttributes('spoke');
    }
    public function __construct(public StartNewPdnsSynchronization $event)
    {
    }

    public function handle(): void
    {
        $attributes = $this->event->getAttributes();

        $attributes['resources'] = ResourceGraph::withProvider($attributes['spoke'])
            ->type('microsoft.network/networkinterfaces')
            ->extend('name', 'tostring(properties.ipConfigurations)')
            ->project('name')
            ->get();

        event(new ReceivedNetworkInterfaces($attributes));
    }
}
