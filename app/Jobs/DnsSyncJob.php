<?php

namespace App\Jobs;

use App\Exceptions\DnsZonesException;
use App\Facades\Pdns;
use App\Traits\DeveloperNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DnsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DeveloperNotification;

    protected array $skipSubscriptions = [
        '534fed2b-6945-40c2-bd1b-9bbba36a2f29', // LHG_MSP_PELE_P
        '10006206-6ed9-41cf-b446-c783f3d71483', // LHG_MSP_PELE_N
    ];

    /**
     * @throws DnsZonesException
     */
    public function handle(): void
    {
        $subscriptionId = config('dnssync.subscription_id');
        $resourceGroup = config('dnssync.resource_group');

        Pdns::withHub('lhg_arm', $subscriptionId, $resourceGroup)
            ->withRecordType(['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT'])
            ->withSpoke('lhg_arm')
            ->skipSubscriptions($this->skipSubscriptions)
            ->sync();
    }

    public function failed($exception = null)
    {
        $this->sendDeveloperNotification($exception);
    }
}
