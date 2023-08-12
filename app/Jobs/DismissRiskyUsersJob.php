<?php

namespace App\Jobs;

use App\Services\AzureAD\RiskyUserProperties;
use App\Services\AzureAD\UserRiskState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DismissRiskyUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {

        if (!config('services.azure-ad.dismis-risky-users')) {
            return;
        }
        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->dismiss();

        if ($dismissedUsers->status() == 204) {
            DismissRiskyUsersJob::dispatch();
        }
    }
}
