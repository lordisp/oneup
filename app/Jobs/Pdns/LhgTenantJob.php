<?php

namespace App\Jobs\Pdns;

use App\Exceptions\DnsZonesException;
use App\Services\Pdns\Pdns;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LhgTenantJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @throws DnsZonesException
     * @throws \Throwable
     */
    public function handle(): void
    {
        if (!config('dnssync.pdns_lhg_enabled')) {
            info('PDNS Sync for LHG is disabled');
            return;
        }

        (new Pdns)
            ->withSpoke('lhg_arm')
            ->withRecordType(['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT', 'CNAME'])
            ->skipSubscriptions([
                '534fed2b-6945-40c2-bd1b-9bbba36a2f29',
                '10006206-6ed9-41cf-b446-c783f3d71483',
            ])
            ->skipZonesForValidation([
                'privatelink.postgres.database.azure.com',
                'privatelink.westeurope.azmk8s.io',
                'privatelink.api.azureml.ms',
                'privatelink.azure-api.net',
            ])
            ->sync();
    }
}
