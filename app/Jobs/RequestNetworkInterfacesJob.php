<?php

namespace App\Jobs;

use App\Exceptions\AzureArm\ResourceGraphException;
use App\Services\AzureArm\ResourceGraph;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestNetworkInterfacesJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $provider)
    {
    }

    public function uniqueId(): string
    {
        return $this->provider;
    }

    /**
     * @throws ResourceGraphException
     */
    public function handle(): void
    {
        info(sprintf("RequestNetworkInterfaces with batchId: %s for %s", $this->batchId, $this->provider));

        (new ResourceGraph)
            ->type('microsoft.network/networkinterfaces')
            ->extend('key', 'id')
            ->extend('value', 'tostring(properties.ipConfigurations)')
            ->project('key,value')
            ->toCache("networkinterfaces:{$this->provider}", config('services.resourcegraph.expire'));
    }
}
