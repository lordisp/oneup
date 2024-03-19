<?php

namespace App\Listeners;

use App\Jobs\ServiceNow\CleanUpFirewallRuleJob;
use App\Models\FirewallRule;
use Illuminate\Support\Facades\Bus;

class CleanUpFirewallRulesListener
{
    public function handle()
    {
        $firewallRules = FirewallRule::with(['request' => fn ($q) => $q->select('id', 'created_at')])
            ->select('id', 'hash', 'action', 'service_now_request_id')
            ->whereAction('delete')
            ->get()
            ->map(fn ($rule) => [
                'id' => $rule->id,
                'hash' => $rule->hash,
                'action' => $rule->action,
                'request_id' => $rule->request->id,
                'created_at' => $rule->request->created_at,
            ]
            );

        foreach ($firewallRules as $firewallRule) {
            $jobs[] = new CleanUpFirewallRuleJob($firewallRule);
        }

        if (isset($jobs)) {
            Bus::batch($jobs)
                ->name('cleanup-firewall-rule-job')
                ->dispatch();
        }
    }
}
