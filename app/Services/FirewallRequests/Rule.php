<?php

namespace App\Services\FirewallRequests;

use App\Models\FirewallRule;

abstract class Rule
{
    const FOREVER = '31.12.2222';

    protected FirewallRule|array $rule;

    public function __construct(FirewallRule|array $rule)
    {
        $this->rule = $rule;
    }
}
