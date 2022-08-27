<?php

namespace App\Services;

use App\Facades\TokenCache;
use App\Models\DnsSyncZone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Response;


/**
 * DnsSync is based on Microsoft's Azure Resource Graph API
 * @link https://docs.microsoft.com/en-us/rest/api/azureresourcegraph/resourcegraph(2021-03-01)/resources
 */
class DnsSync
{
    protected string $scope, $spoke, $hub, $subscriptionId, $resourceGroup;
    protected array $recordType;
    protected bool $isHub = false;
    protected JsonResponse $state;

    public function __construct()
    {
        $this->scope = Str::orderedUuid()->toString();
    }

    /**
     * Authenticate with Spoke
     * @return int
     */
    public function start(): int
    {
        Log::info('Initiate synchronization from ' . $this->spoke . ' to ' . $this->hub);

        $this->state = Response::json('Accepted', 202);

        $zones = $this->queryZones($this->scope, $this->subscriptionId);

        $records = $this->queryRecords($zones);

        $this->cacheRecords($records);

        Log::info('start sync with hub-id:' . $this->subscriptionId);
        $this->sync($this->scope, $zones);

        $this->flushCache();

        $this->state = Response::json(['No Content'], 204);

        return $this->state->status();

    }


    public function withSpoke(string $provider): static
    {
        $this->spoke = $provider;
        return $this;
    }

    public function withHub(string $provider, string $subscriptionId, string $resourceGroup): static
    {
        $this->hub = $provider;
        $this->subscriptionId = $subscriptionId;
        $this->resourceGroup = $resourceGroup;
        return $this;
    }


    public function withRecordType(string|array $type): static
    {
        $this->recordType = is_array($type) ? $type : [$type];
        return $this;
    }

    protected function isRecordType($record): bool
    {
        return in_array(data_get($record, 'name'), $this->recordType);
    }


    /**
     * Authenticate with Hub
     * @param $scope
     * @param $zones
     * @return void
     */
    protected function sync($scope, $zones): void
    {

        $responses = Http::pool(function (Pool $pool) use ($scope, $zones) {

            foreach ($zones as $zone) {

                $records = Cache::tags([$scope, 'records'])->get($zone) ?: [];

                foreach ($records as $record) {

                    /* Validate record-types based on the withRecordType() method */
                    if (in_array(basename(data_get($record, 'type')), $this->recordType)) {

                        $uri = 'https://management.azure.com/subscriptions/' . $this->subscriptionId . '/resourceGroups/' . $this->resourceGroup . '/providers/Microsoft.Network/privateDnsZones/' . basename($zone) . '/' . basename($record['type']) . '/' . $record['name'] . '?api-version=2018-09-01';

                        $request = $this->getEtagFromHubOrCreateNewRequest($uri, $record);

                        if (!Arr::exists($request['headers'], 'Skip')) {

                            $responses[] = $pool->withHeaders($request['headers'])
                                ->withToken($this->token($this->hub))
                                ->retry(20, 200, function ($exception, $request): bool {
                                    Log::warning('sync-pool warning: ' . $exception->getMessage());
                                    $request->withToken($this->token($this->hub));
                                    return true;

                                }, throw: false)
                                ->put($uri, Arr::only($request, ['etag', 'properties']));
                        }
                    }
                }

            }
            if (empty($responses)) Log::info('Nothing to update from' . $this->spoke . ' to ' . $this->hub); else {
                Log::info('updating ' . count($responses) . ' records from ' . $this->spoke . ' to ' . $this->hub);
            }

            return $responses ?? [];

        });
        if (config('app.debug')) {
            foreach ($responses as $response) {
                if ($response instanceof \Illuminate\Http\Client\Response) {

                    $code = $response->status();

                    if ($code >= 400) {
                        Log::warning('Spoke ' . $this->spoke . ' to ' . $this->hub . ': ' . $response->json('message'));
                    } elseif ($code >= 200 && $code < 300) {
                        $properties = $response->json('properties');
                        Arr::forget($properties, ['fqdn']);
                        Log::info('Updated ' . $properties['fqdn'] . ' from ' . $this->spoke . ' to ' . $this->hub, $properties);
                    }
                }
            }
        }
        Log::info('end sync');

    }

    /**
     * Authenticate to Hub
     * @param $uri
     * @param $spokeRecord
     * @return array
     */
    protected function getEtagFromHubOrCreateNewRequest($uri, $spokeRecord): array
    {
        $hubRecord = Http::azure()
            ->withToken($this->token($this->hub))
            ->retry(20, 200, function ($exception, $request): bool {
                if ($exception instanceof ConnectionException && $exception->getCode() === 404) {
                    return false;
                } else {
                    Log::warning('sync warning: ' . $exception->getMessage());
                    $request->withToken($this->token($this->hub));
                    return true;
                }

            }, throw: false)
            ->get($uri)
            ->json();

        return Arr::exists($hubRecord, 'code')
            ? ['properties' => $spokeRecord['properties'], 'headers' => ['If-None-Match' => '*']]
            : ['properties' => $spokeRecord['properties'], 'headers' => $this->skipIfEqual($hubRecord, $spokeRecord), 'etag' => $hubRecord['etag'],];
    }

    protected function skipIfEqual($hubRecord, $spokeRecord): array
    {
        if (json_encode($hubRecord['properties']) == json_encode($spokeRecord['properties'])) {
            //Log::debug('Hub and spoke are equal. Skip update', $spokeRecord);
            return ['Skip' => 'true'];
        } else {
            Log::debug('Spoke differs from hub. Continuing updating ' . $spokeRecord['name'], $spokeRecord);
            return ['If-Match' => $hubRecord['etag']];
        }
    }

    /**
     * Call Microsoft ResourceGraph Explorer and retrieve private-dns zones. Depending on the $isHub property, either spokes or the hub will be queried.
     * This is required to run dns deletion queries in a later version.
     * Result will be cached to Redis with a tag of a random Uuid and `zones`
     *
     * @param string $scope
     * @param string $subscriptionId
     * @return array
     */
    protected function queryZones(string $scope, string $subscriptionId = ''): array
    {
        $zones = DnsSyncZone::all()->pluck('name')->toArray();
        $operator = $this->isHub && Str::isUuid($subscriptionId) ? '==' : '!=';
        $url = 'https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version=2021-03-01';
        $query = 'resources | project  zones = pack_array(' . $this->toString($zones) . ') | mv-expand zones to typeof(string) | join kind = innerunique ( resources | where type == "microsoft.network/privatednszones" and subscriptionId ' . $operator . ' "' . $subscriptionId . '" | project name, id, subscriptionId) on $left.zones == $right.name | project id';

        return Cache::tags([$scope, 'zones'])->rememberForever('zones', fn(): array => Arr::flatten(
            Http::withToken($this->token($this->tokenProvider()))
                ->acceptJson()
                ->retry(20, 200, function ($exception, $request): bool {
                    $request->withToken($this->token($this->tokenProvider()));
                    return true;
                })
                ->post($url, ["query" => $query])
                ->json('data')));
    }

    protected function queryRecords($zones): array
    {
        return Http::pool(function (Pool $pool) use ($zones) {
            foreach ($zones as $zone) {
                $responses[] = $pool->as($zone)
                    ->withToken($this->token($this->spoke))
                    ->retry(20, 200, function ($exception, $request): bool {
                        Log::warning('queryRecords-pool warning: ' . $exception->getMessage());
                        $request->withToken($this->token($this->spoke));
                        return true;
                    }, throw: false)
                    ->get('https://management.azure.com' . $zone . '/ALL?api-version=2018-09-01&$top=1000');
            }
            return $responses ?? [];
        });
    }

    protected function cacheRecords($records): void
    {
        foreach ($records as $key => $value) {
            if ($value instanceof \Illuminate\Http\Client\Response) Cache::tags([$this->scope, 'records'])->put($key, $value->json('value'));
        }
    }

    protected function flushCache(): void
    {
        Cache::tags([$this->scope, 'zones'])->flush();
        Cache::tags([$this->scope, 'records'])->flush();
    }

    /**
     * Convert an array to a KQL array as string
     * @param array $array
     * @return string
     */
    protected function toString(array $array): string
    {
        return '"' . implode('","', $array) . '"';
    }

    /**
     * Acquire an access-token for azure api calls
     * @param $provider
     * @return string
     */
    protected function token($provider): string
    {
        return decrypt(TokenCache::provider($provider)->get());
    }

    protected function tokenProvider(): string
    {
        return $this->isHub ? $this->hub : $this->spoke;
    }

    protected function getManagementLock()
    {
        $url = '/subscriptions/' . $this->subscriptionId . '/resourceGroups/' . $this->resourceGroup . '/providers/Microsoft.Authorization/locks?api-version=2016-09-01';
        return Http::azure()
            ->withToken($this->token($this->hub))
            ->retry(20, 200, function ($exception, $request): bool {
                $request->withToken($this->token($this->hub));
                return true;
            })
            ->get($url)
            ->collect('value')
            ->pluck('id')
            ->first();
    }

}
