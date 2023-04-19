<?php

namespace App\Policies;

use App\Models\FirewallRule;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FirewallRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasFirewallRules()
            || $user->operations()->contains('service-now/firewall/request/readAll');
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
        $key = 'service-now/firewall/request/extendAll';
        $operation = cache()->rememberForever($key, function () use ($key) {
            return Operation::firstOrCreate([
                'operation' => $key,
                'description' => 'Can extend all firewall rules'
            ]);
        });

        return $user->hasBusinessService($rule->businessService->name)
            && isset($rule->new_status)
            && ($rule->status != 'deleted' && $rule->status != 'extended')
            || $user->operations()->contains($operation->operation);
    }

    public function decommission(User $user, FirewallRule $rule): bool
    {
        $key = 'service-now/firewall/request/decommissionAll';
        $operation = cache()->rememberForever($key, function () use ($key) {
            return Operation::firstOrCreate([
                'operation' => $key,
                'description' => 'Can decommission all firewall rules'
            ]);
        });

        return $user->hasBusinessService($rule->businessService->name)
            && isset($rule->new_status)
            && ($rule->status != 'deleted')
            || $user->operations()->contains($operation->operation);
    }

    public function update(User $user, FirewallRule $rule): bool
    {
        $key = $user->id . $rule->id . $rule->business_service;

        return cache()->remember($key, now()->addMinutes(15), function () use ($user, $rule) {
            return $user->hasBusinessService($rule->business_service);
        });

    }

    public function delete(User $user, FirewallRule $rule): bool
    {
        $key = 'service-now/firewall/request/delete';
        $operation = cache()->rememberForever($key, function () use ($key) {
            return Operation::firstOrCreate([
                'operation' => $key,
                'description' => 'Can delete firewall rules'
            ]);
        });
        return $user->operations()->contains($operation->operation);
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