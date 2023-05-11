<?php

namespace App\Jobs;

use App\Exceptions\DnsZonesException;
use App\Facades\Pdns;
use App\Traits\DeveloperNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DnsSyncAviatarJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DeveloperNotification;

    /**
     * @throws DnsZonesException
     */
    public function handle(): void
    {
        $subscriptionId = config('dnssync.subscription_id');
        $resourceGroup = config('dnssync.resource_group');

        Pdns::withHub('lhg_arm', $subscriptionId, $resourceGroup)
            ->withRecordType(['A','CNAME'])
            ->withSpoke('aviatar_arm')
            ->sync();
    }

    public function failed($exception = null)
    {
        $this->sendDeveloperNotification($exception);
    }
}
