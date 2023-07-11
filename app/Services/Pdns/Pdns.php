<?php

namespace App\Services\Pdns;

use App\Exceptions\DnsZonesException;
use App\Jobs\Pdns\PdnsQueryZoneRecordsJob;
use App\Jobs\RequestNetworkInterfacesJob;
use App\Models\DnsSyncZone;
use App\Traits\Token;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Pdns
{
    use Token;

    protected string $hub = 'lhg_arm', $spoke = 'lhg_arm', $subscriptionId, $resourceGroup;

    protected array $recordType = ['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT'];

    protected array $zones = [];

    protected array $withSubscriptions = [];

    protected array $skippedSubscriptions = [];

    protected bool $isHub = false;

    protected array $skippedZonesForValidation = [];

    public function __construct()
    {
        $this->subscriptionId = config('dnssync.subscription_id');
        $this->resourceGroup = config('dnssync.resource_group');
    }


    /**
     * @throws DnsZonesException|\Throwable
     */
    public function sync(): void
    {
        $zones = $this->getZones();

        Log::debug(sprintf("Processing %s Zones", count($zones)));

        $jobs[] = new RequestNetworkInterfacesJob($this->spoke);

        foreach ($zones as $zone) {
            $attributes = [
                'zone' => $zone,
                'hub' => $this->hub,
                'spoke' => $this->spoke,
                'recordType' => $this->recordType,
                'subscriptionId' => $this->subscriptionId,
                'resourceGroup' => $this->resourceGroup,
                'skippedZonesForValidation' => $this->skippedZonesForValidation,
            ];
            $jobs[] = new PdnsQueryZoneRecordsJob($attributes);
        }

        if (count($jobs) === 1) {
            return;
        }

        $chunk = config('services.pdns.chunk.zones');

        if (count($jobs) > $chunk) {
            $jobs = array_chunk($jobs, $chunk);
        }

        Bus::batch($jobs)
            ->name('zones')
            ->dispatch();
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
        return Arr::flatten(
            Http::withToken(decrypt($this->token($this->tokenProvider())))
                ->retry(200, 0, function ($exception, $request): bool {
                    if ($exception instanceof RequestException) {
                        info(sprintf("Retry 'Pdns::getZones' because of %s", $exception->response->reason()));
                    }

                    if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                        return true;
                    }
                    $request->withToken(decrypt($this->newToken($this->hub)));
                    return true;
                }, false)
                ->post(
                    'https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version=2021-03-01',
                    ["query" => sprintf(
                        "resources | project  zones = pack_array(%s) | mv-expand zones to typeof(string) | join kind = innerunique ( resources | where type == \"microsoft.network/privatednszones\" and subscriptionId  != \"%s\" %s %s| project name, id, subscriptionId) on \$left.zones == \$right.name | project id",
                        $this->toString($this->getReferenceZones()),
                        $this->subscriptionId,
                        $this->getWithSubscriptionsQuery(),
                        $this->getSkippedSubscriptionsQuery()
                    )]
                )
                ->onError(fn($exception) => throw new DnsZonesException($exception->reason(), $exception->status()))
                ->json('data')
        );
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
        $this->withSubscriptions = (array)$subscriptionIds;
        return $this;
    }

    public function skipSubscriptions(string|array $subscriptionIds): static
    {
        $this->skippedSubscriptions = is_array($subscriptionIds) ? $subscriptionIds : [$subscriptionIds];
        return $this;
    }

    public function skipZonesForValidation(string|array $zones): static
    {
        $this->skippedZonesForValidation = is_array($zones) ? $zones : [$zones];
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

    protected function getWithSubscriptionsQuery(): ?string
    {
        return !empty($this->withSubscriptions)
            ? Normalize::withSubscriptionsQuery($this->withSubscriptions)
            : null;
    }

    protected function getSkippedSubscriptionsQuery(): ?string
    {
        return !empty($this->skippedSubscriptions)
            ? Normalize::skippedSubscriptionsQuery($this->skippedSubscriptions)
            : null;
    }
}