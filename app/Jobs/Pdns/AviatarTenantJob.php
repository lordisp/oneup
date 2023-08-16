<?php

namespace App\Jobs\Pdns;

use App\Services\Pdns\Pdns;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AviatarTenantJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (!config('dnssync.pdns_aviatar_enabled')) {
            info('PDNS Sync for Aviatar is disabled');
            return;
        }

        (new Pdns)
            ->withSpoke('aviatar_arm')
            ->withRecordType(['A', 'CNAME'])
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
}
