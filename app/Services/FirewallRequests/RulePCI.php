<?php

namespace App\Services\FirewallRequests;

use App\Models\FirewallRule;
use App\Services\Filter\IPAddressFilter;

class RulePCI extends Rule
{
    protected mixed $container;

    public static function reset($rule): FirewallRule
    {
        return (new self($rule))
            ->getIps()
            ->setPci()
            ->save();
    }

    private function getIps(): static
    {
        $connections = array_merge(
            json_decode($this->rule->source, true),
            json_decode($this->rule->destination, true)
        );

        data_set(
            $this->container,
            'IpArray',
            IPAddressFilter::process($connections)
        );

        return $this;
    }

    private function setPci(): static
    {
        $this->rule->pci_dss = PCIValidator::bySubnet($this->container['IpArray']);

        return $this;
    }


    private function save(): array|FirewallRule
    {
        $this->rule->save();
        return $this->rule;
    }
}