<?php

namespace App\Jobs\Webhook;

use App\Jobs\Scim\UpdateUserJob;
use App\Jobs\WebhookJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScimRemoveMemberJob extends WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $graphProvider = 'lhg_graph';

    protected string $armProvider = 'webhook_log_analytics';

    protected string $scope = '/subscriptions/881a97ff-f77b-4f60-a853-c10be1183568/resourcegroups/rg_fnds_governance_monitoring/providers/microsoft.operationalinsights/workspaces/log-lhg-ams-governance-default';

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->updateState('Acknowledged', $this->scope, 'processing disable-user');
        $members = $this->getMembers();
        foreach ($members as $member) {
            UpdateUserJob::dispatch($member, $this->graphProvider)->onQueue('webhook');
        }
        $this->updateState('Closed', $this->scope, 'user-import processed');
    }
}
