<?php

namespace App\Jobs\Pdns;

use App\Jobs\RequestNetworkInterfacesJob;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class PrivateDnsSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $providers = [];

    public function __construct()
    {
        $this->providers = explode(',', trim(config('dnssync.provider')));
    }

    public function handle(): void
    {
        $this->dispatchJobs();
    }

    private function dispatchJobs(): void
    {
        if (empty($this->providers)) {
            Log::info('No providers found to dispatch jobs');
            return;
        }
        $jobs = [];

        foreach ($this->providers as $provider) {
            $jobs[] = new RequestNetworkInterfacesJob($provider);
        }

        if (empty($jobs)) {
            Log::info('PrivateDnsSync: No jobs found to dispatch');
            return;
        }

        Bus::batch($jobs)
            ->allowFailures()
            ->name('pdns')
            ->onQueue(config('dnssync.queue_name'))
            ->finally(function (Batch $batch) {
                Log::info('Private DNS Sync batch jobs dispatched', ['batchId' => $batch->id]);
            })
            ->dispatch();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to dispatch batch jobs', ['exception' => $exception]);
    }
}