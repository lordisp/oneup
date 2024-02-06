<?php

namespace App\Jobs\Pdns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RecordHasResourceJob
{
    public function __construct(protected string $type, protected mixed $records, protected array $resources)
    {
    }

    public function handle(): bool
    {
        if ($this->type === 'PTR') {
            return $this->ptrRecords();
        }

        if ($this->type === 'A') {
            return $this->aRecords();
        }

        Log::debug("Handle other Records");

        return true;
    }

    private function ptrRecords(): bool
    {
        $ptrRecords = data_get($this->records, 'properties.ptrRecords.*.ptrdname');

        $resourceMatch = array_sum(
            array_map(function ($key) use ($ptrRecords) {
                $resource = json_decode($key, true);
                $fqdns = Arr::flatten(data_get($resource, '*.properties.privateLinkConnectionProperties.fqdns')) ?: [];
                return count(array_intersect($ptrRecords, $fqdns));
            }, $this->resources)
        );

        Log::debug(sprintf("PTR Records: %s", $resourceMatch));
        return $resourceMatch > 0;
    }

    private function aRecords(): bool
    {
        Log::debug("A Records", [
            'records' => $this->records,
        ]);

        $ipv4Address = data_get($this->records, 'properties.aRecords.*.ipv4Address');

        $resourceMatch = array_sum(
            array_map(function ($key) use ($ipv4Address) {
                $resourceIPAddress = data_get(json_decode($key, true), '*.properties.privateIPAddress') ?: [];

                Log::debug("Debug aRecords", [
                    'resourceIPAddress' => $resourceIPAddress,
                    'ipv4Address' => $ipv4Address,
                    'intersect' => array_intersect($ipv4Address, $resourceIPAddress),
                ]);

                return count(array_intersect($ipv4Address, $resourceIPAddress));
            }, $this->resources)
        );

        Log::debug(sprintf("A Records: %s", $resourceMatch));
        return $resourceMatch > 0;
    }
}
