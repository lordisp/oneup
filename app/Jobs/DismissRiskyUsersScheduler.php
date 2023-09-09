<?php

namespace App\Jobs;

use App\Services\AzureAD\RiskyUserProperties;
use App\Services\AzureAD\RiskyUserTop;
use App\Services\AzureAD\UserRiskState;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DismissRiskyUsersScheduler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (!config('services.azure-ad.dismiss-risky-users')) {
            Log::warning('Dismiss-Risky-Users is disabled');
            return;
        }
        Log::info('Run Risky-Users Scheduler');

        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        if ($dismissedUsers instanceof Batch) {
            DismissRiskyUsersScheduler::dispatch()
                ->delay(now()->addMinute());
        }
    }
}
