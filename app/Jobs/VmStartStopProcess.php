<?php

namespace App\Jobs;

use App\Events\VmStateChangeEvent;
use App\Exceptions\AzureArm\ResourceGraphException;
use App\Services\AzureArm\ResourceGraph;
use App\Traits\Token;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class VmStartStopProcess implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token;

    protected string $timezone = '';

    public function __construct(protected array $server)
    {
        $this->timezone = config('services.scheduler.vm-start-stop-scheduler.timezone');
    }

    public function handle(): void
    {
        $now = now()->setTimezone($this->timezone);

        if ($this->server['week'] == 'mon-fri' && !$now->isWeekday() || $this->server['status'] == 'disabled') {
            return;
        }

        $serverState = $this->getServerState();

        if (empty($serverState)) {
            Log::warning(sprintf("Server %s not found!", $this->server['vmName']),['VmStartStop']);
            return;
        }

        if ($this->shouldStartServer($now, $this->server['from'], $this->server['to'], data_get($serverState, 'powerState'), data_get($serverState, 'provisioningState'))) {

            if ($this->server['status'] == 'simulate') {
                Log::info(sprintf("Simulate start %s", $this->server['vmName']),['VmStartStop']);
                return;
            }

            event(new VmStateChangeEvent('start', data_get($serverState, 'id'), $this->server['vmName']));

            return;
        }

        if ($this->shouldStopServer($now, $this->server['from'], $this->server['to'], data_get($serverState, 'powerState'), data_get($serverState, 'provisioningState'))) {

            if ($this->server['status'] == 'simulate') {
                Log::info(sprintf("Simulate deallocate %s", $this->server['vmName']),['VmStartStop']);
                return;
            }

            event(new VmStateChangeEvent('deallocate', data_get($serverState, 'id'), $this->server['vmName']));
        }

    }

    /**
     * @throws ResourceGraphException
     */
    private function getServerState(): array
    {
        $result = (new ResourceGraph)
            ->type('microsoft.compute/virtualmachines')
            ->extend('powerState', 'properties.extended.instanceView.powerState.code')
            ->extend('provisioningState', 'properties.provisioningState')
            ->where('name', '==', $this->server['vmName'])
            ->where('subscriptionId', '==', $this->server['subscription'])
            ->take(1)
            ->project('id,powerState,provisioningState')
            ->get();

        return Arr::first($result) ?? [];
    }

    private function shouldStartServer(Carbon $now, Carbon $from, Carbon $to, string $powerState, string $provisioningState): bool
    {
        if ($provisioningState != 'Succeeded') {
            $this->logInvalidProvisioningState();
            return false;
        }

        if ($powerState == 'PowerState/running' || $powerState == 'PowerState/starting') {
            return false;
        }

        return $now >= $from && $now <= $to;
    }

    private function shouldStopServer(Carbon $now, Carbon $from, Carbon $to, string $powerState, string $provisioningState): bool
    {
        if ($provisioningState != 'Succeeded') {
            $this->logInvalidProvisioningState();
            return false;
        }

        if ($powerState == 'PowerState/deallocated') {
            return false;
        }

        if ($powerState == 'PowerState/stopped') {
            return true;
        }

        if ($powerState == 'PowerState/starting') {
            return false;
        }

        return $now < $from || $now > $to;
    }

    private function logInvalidProvisioningState(): void
    {
        Log::error(sprintf("%s has an invalid provisioning state", $this->server['vmName']),['VmStartStop']);
    }
}
