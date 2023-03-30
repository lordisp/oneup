<?php

namespace App\Services\FirewallRequests;

use App\Models\Subnet;
use IPv4\SubnetCalculator;

class PCIValidator
{

    public function __construct(protected array $model)
    {
    }

    public static function bySubnet(array $ipAddresses)
    {
        $model = [
            'ipAddresses' => $ipAddresses,
        ];
        return (new self($model))->handle();
    }

    protected function handle()
    {
        if (array_key_exists('ipAddresses', $this->model)) {
            return $this
                ->getPciNetworks()
                ->isInPciScope();
        }
        return $this;
    }

    private function getPciNetworks(): static
    {
        $subnets = Subnet::whereNotNull('pci_dss')
            ->select(['name', 'size'])
            ->get();

        $this->model['Networks'] = $subnets;

        return $this;
    }

    private function isInPciScope(): bool
    {
        $this->model['pci_dss'] = false;
        foreach ($this->model['Networks'] as $pciSubnet) {
            foreach ($this->model['ipAddresses'] as $ipAddress) {
                if ($this->isIPAddressInSubnet(
                    ipAddress: $ipAddress,
                    subnet: $pciSubnet->name,
                    size: $pciSubnet->size
                )) $this->model['pci_dss'] = true;
            }
        }
        return $this->model['pci_dss'];
    }

    private function isIPAddressInSubnet(string $ipAddress, string $subnet, int $size): bool
    {
        return (new SubnetCalculator($subnet, $size))->isIPAddressInSubnet($ipAddress);
    }
}