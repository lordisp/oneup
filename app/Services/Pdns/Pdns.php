<?php

namespace App\Services\Pdns;

use App\Jobs\Pdns\PdnsQueryZoneRecordsJob;
use App\Models\DnsSyncZone;
use App\Traits\Token;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The crazy DNS Sync
 */
class Pdns
{
    use Token;

    protected string $hub = 'lhg_arm', $spoke = 'lhg_arm', $subscriptionId, $resourceGroup;

    protected array $recordType = ['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT'];

    protected array $zones = [];

    protected array $withSubscriptions = [];

    protected array $skippedSubscriptions = [];

    protected bool $isHub = false;

    public function __construct()
    {
        $this->subscriptionId = config('dnssync.subscription_id');
        $this->resourceGroup = config('dnssync.resource_group');
    }


    public function sync(): void
    {
        $zones = $this->getZones();

        Log::debug(sprintf("Processing %s Zones", count($zones)));

        foreach ($zones as $zone) {
            PdnsQueryZoneRecordsJob::dispatch(
                $zone,
                $this->hub,
                $this->spoke,
                $this->recordType,
                $this->subscriptionId,
                $this->resourceGroup
            );
        }
    }

    protected function getReferenceZones(): array
    {
        return DnsSyncZone::query()
            ->when($this->zones, fn($query) => $query->whereIn('name', $this->zones))
            ->get()
            ->pluck('name')
            ->toArray();
    }

    protected function getZones(): array
    {
        $uri = 'https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version=2021-03-01';

        $withSubscriptionsQuery = !empty($this->withSubscriptions)
            ? Normalize::withSubscriptionsQuery($this->withSubscriptions)
            : null;

        $skippedSubscriptionsQuery = !empty($this->skippedSubscriptions)
            ? Normalize::skippedSubscriptionsQuery($this->skippedSubscriptions)
            : null;

        $query = sprintf(
            "resources | project  zones = pack_array(%s) | mv-expand zones to typeof(string) | join kind = innerunique ( resources | where type == \"microsoft.network/privatednszones\" and subscriptionId  != \"%s\" %s %s| project name, id, subscriptionId) on \$left.zones == \$right.name | project id",
            $this->toString($this->getReferenceZones()),
            $this->subscriptionId,
            $withSubscriptionsQuery,
            $skippedSubscriptionsQuery
        );

        return Arr::flatten(
            Http::withToken(decrypt($this->token($this->tokenProvider())))
                ->acceptJson()
                ->retry(20, 10, function ($exception, $request): bool {
                    $request->withToken(decrypt($this->token($this->tokenProvider())));
                    return true;
                })
                ->post($uri, ["query" => $query])
                ->json('data'));
    }

    public function withHub(string $provider, string $subscriptionId, string $resourceGroup): static
    {
        $this->hub = $provider;
        $this->subscriptionId = $subscriptionId;
        $this->resourceGroup = $resourceGroup;
        return $this;
    }

    public function withSpoke(string $provider): static
    {
        $this->spoke = $provider;
        return $this;
    }

    public function withRecordType(string|array $type): static
    {
        $this->recordType = is_array($type) ? $type : [$type];
        return $this;
    }

    public function withZones(string|array $zones): static
    {
        $this->zones = is_array($zones) ? $zones : [$zones];
        return $this;
    }

    public function withSubscriptions(string|array $subscriptionIds): static
    {
        $this->withSubscriptions = is_array($subscriptionIds) ? $subscriptionIds : [$subscriptionIds];
        return $this;
    }

    public function skipSubscriptions(string|array $subscriptionIds): static
    {
        $this->skippedSubscriptions = is_array($subscriptionIds) ? $subscriptionIds : [$subscriptionIds];
        return $this;
    }

    protected function toString(array $array): string
    {
        return '"' . implode('","', $array) . '"';
    }

    protected function tokenProvider(): string
    {
        return $this->isHub ? $this->hub : $this->spoke;
    }
}