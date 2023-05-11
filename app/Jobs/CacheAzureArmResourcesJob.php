<?php

namespace App\Jobs;

use App\Facades\AzureArm\ResourceGraph;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CacheAzureArmResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $provider = 'lhg_arm')
    {
    }

    public function retryUntil(): Carbon
    {
        return now()->addMinutes(5);
    }

    public function handle(): void
    {
        ResourceGraph::withProvider($this->provider)
            ->type('microsoft.network/networkinterfaces')
            ->extend('name', 'tostring(properties.ipConfigurations)')
            ->project('name')
            ->cache();
    }
}
