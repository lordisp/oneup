<?php

namespace App\Policies;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\FirewallRule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FirewallRulePolicy
{
    use HandlesAuthorization, WithRbacCache;

    public function viewAny(User $user): bool
    {
        return $user->hasFirewallRules()
            || $user->operations()->contains(
                $this->updateOrCreate('serviceNow/firewall/request/readAll', 'Can read all firewall-requests')
            );
    }

    public function view(User $user, FirewallRule $firewallRule): bool
    {
        return $user->hasBusinessService($firewallRule->business_service);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function extend(User $user, FirewallRule $rule): bool
    {
        return $user->hasBusinessService($rule->businessService->name)
            && isset($rule->new_status)
            && ($rule->status != 'deleted' && $rule->status != 'extended' || $rule->last_review <= now()->subDays(90))
                || $user->operations()->contains(
                    $this->updateOrCreate('serviceNow/firewall/request/extendAll', 'Can extend all firewall rules')
                )
                && ($rule->status != 'deleted' && $rule->status != 'extended');
    }

    public function decommission(User $user, FirewallRule $rule): bool
    {
        return $user->hasBusinessService($rule->businessService->name)
            && isset($rule->new_status)
            && ($rule->status != 'deleted')
            || $user->operations()->contains(
                $this->updateOrCreate('serviceNow/firewall/request/decommissionAll', 'Can decommission all firewall rules')
            );
    }

    public function update(User $user, FirewallRule $rule): bool
    {
        $key = $user->id . $rule->id . $rule->business_service;

        return cache()->tags('rbac')->remember($key, now()->addMinutes(15), function () use ($user, $rule) {
            return $user->hasBusinessService($rule->business_service);
        });

    }

    public function delete(User $user, FirewallRule $rule): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('service-now/firewall/request/delete', 'Can delete firewall rules')
        );
    }

    public function restore(User $user, FirewallRule $firewallRule): bool
    {
        return false;
    }

    public function forceDelete(User $user, FirewallRule $firewallRule): bool
    {
        return false;
    }
}