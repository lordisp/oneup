<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class PdnsSyncBatchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->syncMain();
        $this->syncAviatar();
    }

    protected function syncMain()
    {
        Bus::chain([
            new CacheAzureArmResourcesJob,
            new DnsSyncJob,
        ])->dispatch();
    }

    protected function syncAviatar()
    {
        Bus::chain([
            new CacheAzureArmResourcesJob('aviatar_arm'),
            new DnsSyncAviatarJob,
        ])->dispatch();
    }
}
