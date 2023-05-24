<?php

namespace App\Services\AzureArm;

use App\Exceptions\AzureArm\ResourceGraphException;
use App\Facades\Redis;
use App\Traits\Token;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResourceGraph
{
    use Token;

    const VERSION = '2021-03-01';
    const URI = "https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version=" . self::VERSION;

    protected string $provider = 'lhg_arm';
    protected string $withSubscription;
    protected string $name;
    protected array $type;
    protected array $extend;
    protected array $where;
    protected array $project;
    protected string $token = '';

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
     * @throws ResourceGraphException
     */
    public function toCache(string $name): bool
    {
        $id = 0;

        $results = $this->call();

        $array_map = [];

        $name = Str::lower($name);

        foreach (Arr::flatten(data_get($results, 'data')) as $key => $value) {
            $keyString = (string)$id;
            $array_map[$key] = Redis::hSet($name, "{$name}:{$keyString}", $value);
            $id++;
        }

        $skipToken = data_get($results, '$skipToken');

        while (!empty($skipToken)) {
            $results = $this->call($skipToken);

            foreach (Arr::flatten(data_get($results, 'data')) as $key => $value) {
                $keyString = (string)$id;
                $array_map[$key] = Redis::hSet($name, "{$name}:{$keyString}", $value);
                $id++;
            }

            $skipToken = data_get($results, '$skipToken');
        }

        return array_sum($array_map) > 0;
    }

    public function deleteCache($name): bool
    {
        $name = Str::lower($name);

        foreach (Redis::hKeys($name) as $hKey) {
            $map[] = Redis::hDel($name, $hKey);
        }

        return isset($map) && array_sum($map) > 0;
    }

    public function fromCache($name)
    {
        return Redis::hGetAll(Str::lower($name));
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
    public function withSubscription(string $subscriptionId, string $operator = '=='): static
    {
        if (!Str::isUuid($subscriptionId)) {
            throw new ResourceGraphException('The Subscription-Id is not a valid UUID!');
        }

        $this->withSubscription = "subscriptionId {$operator} '{$subscriptionId}'";
        return $this;
    }

    public function withToken($token): static
    {
        $this->token = $token;
        return $this;
    }

    protected function call($skipToken = null)
    {
        if ($skipToken) {
            $body['options'] = ['$skipToken' => $skipToken];
        }

        $body['query'] = $this->queryBuilder();

        $token = !empty($this->token) ? $this->token : $this->token($this->provider);

        return Http::withToken(decrypt($token))
            ->retry(200, 0, function ($exception, $request) {

                Log::debug("Retry because {$exception->getMessage()}", (array)$exception);

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