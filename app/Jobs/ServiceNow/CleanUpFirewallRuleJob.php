<?php

namespace App\Jobs\ServiceNow;

use App\Exceptions\CleanUpFirewallRuleJobException;
use App\Models\FirewallRule;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanUpFirewallRuleJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $firewallRule;

    public function __construct(array $firewallRule)
    {
        $this->firewallRule = $firewallRule;
    }

    /**
     * @throws CleanUpFirewallRuleJobException
     *
     * @description This job will clean up the firewall rule that was created by the previous Service-Now request.
     */
    public function handle()
    {
        $rule = FirewallRule::with(['request' => fn ($request) => $request
            ->where('created_at', '<', $this->firewallRule['created_at'])
            ->select('id', 'created_at')])
            ->select('id', 'hash', 'action', 'service_now_request_id')
            ->where('hash', $this->firewallRule['hash'])
            ->where('action', 'add');

        if (! $rule->count()) {
            return;
        }

        if ($this->updateStatus($rule)) {
            $this->setAudit($rule);
        }
    }

    /**
     * @throws CleanUpFirewallRuleJobException
     *
     * @description This method will update the status of the firewall rule to deleted.
     */
    protected function updateStatus($rule): bool
    {
        try {
            return $rule->update(['status' => 'deleted']);
        } catch (\Exception $exception) {
            throw new CleanUpFirewallRuleJobException($exception);
        }
    }

    /**
     * @description This method will create an audit log for the firewall rule that was decommissioned by a previous Service-Now request.
     */
    protected function setAudit($rule): void
    {
        $rule->first()->audits()->create([
            'actor' => 'Previous Service-Now Request',
            'activity' => 'Decommission Rule',
            'status' => 'Success',
        ]);
    }
}
