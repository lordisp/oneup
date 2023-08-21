<?php

namespace App\Services\FirewallRequests;

use App\Models\FirewallRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RuleStatus extends Rule
{
    const ReviewIterationMonths = 6;

    public static function reset($rule): FirewallRule
    {
        return (new self($rule))
            ->resetStatus()
            ->rule;
    }

    private function resetStatus(): static
    {
        $rule = $this->rule;
        if ($this->rule->last_review <= now()->subMonths(self::ReviewIterationMonths)
            && $this->rule->action === 'add'
            && $this->rule->status !== 'deleted'
            && $this->rule->status !== 'open'
            && Carbon::parse($this->rule->end_date) > now()
        ) {
            $this->rule->status = 'open';
            $this->rule->save();
            Log::info("UPDATED STATUS: reset status from '{$rule->status}' to 'open'", [
                'id' => $rule->id,
                'previews_status' => $rule->status,
                'status' => 'open',
            ]);
        }
        return $this;
    }
}