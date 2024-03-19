<?php

namespace App\Jobs\Pdns;

use App\Services\Pdns\Pdns;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPrivateDnsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $provider)
    {
    }

    public function handle(): void
    {
        (new Pdns)
            ->withSpoke($this->getProvider())
            ->withRecordType(['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT', 'CNAME'])
            ->skipZonesForValidation([
                'privatelink.postgres.database.azure.com',
                'privatelink.westeurope.azmk8s.io',
                'privatelink.api.azureml.ms',
                'privatelink.azure-api.net',
                'privatelink.mysql.database.azure.com',
                'privatelink.westeurope.kusto.windows.net',
                'privatelink.northeurope.kusto.windows.net',
                'privatelink.kusto.azuresynapse.net',
            ])
            ->sync();
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}
