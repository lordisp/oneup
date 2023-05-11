<?php

namespace App\Services\AzureArm;

use App\Exceptions\AzureArm\ResourceGraphException;
use App\Traits\Token;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResourceGraph
{
    use Token;

    const VERSION = '2021-03-01';
    const URI = "https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version=" . self::VERSION;
    const TAG = 'resourcegraph';

    protected string $provider = 'lhg_arm';
    protected string $withSubscription;
    protected string $name;
    protected array $type;
    protected array $extend;
    protected array $where;
    protected array $project;

    /**
     * @throws ResourceGraphException
     */
    public function cache(): void
    {
        $results = $this->call();

        $skipToken = $this->toCache($results);

        while (!empty($skipToken)) {
            $results = $this->call($skipToken);

            $skipToken = $this->toCache($results);
        }
    }

    /**
     * @throws ResourceGraphException
     */
    public function get(): array
    {
        $results = $this->call();

        $skipToken = data_get($results, '$skipToken');

        while (!empty($skipToken)) {
            $result = $this->call($skipToken);

            $results['data'] = array_merge($results['data'], $result['data']);

            $skipToken = data_get($result, '$skipToken');
        }

        return data_get($results, 'data') ?: [];
    }

    /**
     * @param string $provider
     * @return ResourceGraph
     * @throws ResourceGraphException
     */
    public function withProvider(string $provider): static
    {
        if (empty($provider)) {
            throw new ResourceGraphException('The name must be a valid string!');
        }

        $this->provider = $provider;
        return $this;
    }


    /**
     * @throws ResourceGraphException
     */
    public function name(string $name, string $operator = 'has'): static
    {
        if (empty($name)) {
            throw new ResourceGraphException('The name must be a valid string!');
        }

        $this->name = "name {$operator} '{$name}'";
        return $this;
    }

    /**
     * @throws ResourceGraphException
     */
    public function type(string $resourceType, string $operator = '=='): static
    {
        if (empty($resourceType)) {
            throw new ResourceGraphException('The type must be a valid string!');
        }

        $this->type[] = "type {$operator} '{$resourceType}'";
        return $this;
    }

    public function extend(string $key, string $value): static
    {
        $this->extend[] = "extend {$key} = {$value}";
        return $this;
    }

    public function where(string $key, string $operator, string $value): static
    {
        $this->where[] = "where {$key} {$operator} '{$value}'";
        return $this;
    }

    public function project(string|array $attributes): static
    {
        $attributes = implode(',', (array)$attributes);

        $this->project[] = "project {$attributes}";
        return $this;
    }

    /**
     * @throws ResourceGraphException
     */
    public function withSubscription(string $subscriptionId = null, string $operator = '=='): static
    {
        if (!isset($subscriptionId)) {
            return $this;
        }

        if (!Str::isUuid($subscriptionId)) {
            throw new ResourceGraphException('The Subscription-Id is not a valid UUID!');
        }

        $this->withSubscription = "subscriptionId {$operator} '{$subscriptionId}'";
        return $this;
    }

    protected function call($skipToken = null)
    {
        if ($skipToken) {
            $body['options'] = ['$skipToken' => $skipToken];
        }

        $body['query'] = $this->queryBuilder();

        return Http::withToken(decrypt($this->token($this->provider)))
            ->retry(200, 0, function ($exception, $request) {

                Log::debug("Retry because {$exception->getMessage()}");

                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }

                $request->withToken(decrypt($this->newToken($this->provider)));

                return true;

            }, throw: false)
            ->post(self::URI, $body)
            ->onError(fn($exception) => throw new ResourceGraphException($exception->reason(), $exception->status()))
            ->json();
    }

    /**
     * @throws ResourceGraphException
     */
    protected function toCache($results)
    {
        $tag = self::TAG . '_' . $this->provider;

        try {
            $cached = cache()->tags([$tag])->get($tag) ?: [];
        } catch (\Exception $exception) {
            throw new ResourceGraphException($exception->getMessage());
        }

        $unique = array_unique(array_merge(
            data_get($results, 'data.*.name') ?: [],

            $cached
        ));

        try {
            cache()->tags([$tag])->put($tag, $unique);
        } catch (\Exception $exception) {
            throw new ResourceGraphException($exception->getMessage());
        }

        return data_get($results, '$skipToken');
    }

    /**
     * @throws ResourceGraphException
     */
    public static function fromCache(string $provider = 'lhg_arm'): array
    {
        $tag = self::TAG . '_' . $provider;

        try {
            return cache()->tags([$tag])->get($tag) ?: [];
        } catch (\Exception $exception) {
            throw new ResourceGraphException($exception->getMessage());
        }

    }

    protected function queryBuilder(): string
    {
        $options = [];

        if (isset($this->name)) {
            $options[] = $this->name;
        }

        if (isset($this->type)) {
            $options[] = implode(' and ', $this->type);
        }

        if (isset($this->extend)) {
            $options[] = implode(' | ', $this->extend);
        }

        if (isset($this->where)) {
            $options[] = implode(' | ', $this->where);
        }

        if (isset($this->project)) {
            $options[] = implode(' | ', $this->project);
        }

        if (isset($this->withSubscription)) {
            $options[] = $this->withSubscription;
        }

        array_map(fn($key) => " {$key} ", $options);

        $where = $options ? sprintf("| where %s", implode(' | ', $options)) : null;

        return "resources {$where}";
    }
}