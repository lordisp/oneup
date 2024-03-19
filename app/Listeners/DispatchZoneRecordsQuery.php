<?php

namespace App\Listeners;

use App\Events\NetworkInterfacesCached;
use Illuminate\Support\Facades\Bus;

class DispatchZoneRecordsQuery
{
    private int $chunk;

    public function __construct()
    {
        $this->chunk = config('services.pdns.chunk.zones');
    }

    public function handle(NetworkInterfacesCached $event): void
    {
        $jobs = $event->getZoneRecordJobs();

        if (count($jobs) === 0) {
            return;
        }

        $jobs = $this->chunkJobs($jobs);
        $this->dispatchJobs($jobs);
    }

    private function chunkJobs(array $jobs): array
    {
        return (count($jobs) > $this->chunk) ? array_chunk($jobs, $this->chunk) : $jobs;
    }

    private function dispatchJobs(array $jobs): void
    {
        try {
            Bus::batch($jobs)->dispatch();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
