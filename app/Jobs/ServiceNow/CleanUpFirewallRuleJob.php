<?php

namespace App\Jobs\ServiceNow;

use App\Models\FirewallRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanUpFirewallRuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $firewallRule;

    public function __construct(array $firewallRule)
    {
        $this->firewallRule = $firewallRule;
    }

    public function handle()
    {
        $rule = FirewallRule::with(['request' => fn($request) => $request
            ->where('created_at', '<', $this->firewallRule['created_at'])
            ->select('id', 'created_at')])
            ->select('id', 'hash', 'action', 'service_now_request_id')
            ->where('hash', $this->firewallRule['hash'])
            ->where('action', 'add');

        $rule->update(['status' => 'deleted']);

        $rule->first()->audits()->create([
            'actor' => 'Previous Service-Now Request',
            'activity' => 'Decommission Rule',
            'status' => 'Success'
        ]);
    }
}