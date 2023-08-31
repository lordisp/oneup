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

    protected string $provider = 'lhg_arm';
    protected string $withSubscription;
    protected string $name;
    protected array $type;
    protected array $extend;
    protected array $where;
    protected array $project;
    protected string $take;
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
    public function toCache(string $hash, int $expireSeconds = null): array
    {
        $results = $this->call();

        $cached = $this->addToCache($hash, $results);

        $skipToken = data_get($results, '$skipToken');

        while (!empty($skipToken)) {
            $results = $this->call($skipToken);

            $cached = array_merge($cached, $this->addToCache($hash, $results));

            $skipToken = data_get($results, '$skipToken');
        }

        if (isset($expireSeconds)) {
            Redis::expire($hash, $expireSeconds);
        }

        return $cached;
    }

    public function deleteCache($name): bool
    {
        foreach (Redis::hKeys($name) as $hKey) {
            $map[] = Redis::hDel($name, $hKey);
        }

        return isset($map) && array_sum($map) > 0;
    }

    public function fromCache($name, $keys = false): array
    {
        if ($keys) {
            return Redis::hGetAll($name);
        }

        return Redis::hVals($name);
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

    public function take(int $number): static
    {
        $this->take = "take {$number}";
        return $this;
    }

    public function project(string|array ...$attributes): static
    {
        $attributes = implode(',', $attributes);

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
            ->retry(10, 0, function ($exception, $request) {
                if ($exception instanceof RequestException and $exception->getCode() === 429) {
                    sleep(($exception->response->header('Retry-After') ?? 10));
                    return true;
                }

                Log::debug("Retry because {$exception->getMessage()}", (array)$exception);

                if ($exception instanceof RequestException && $exception->response->status() === 400) {
                    return false;
                }

                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }

                $request->withToken(decrypt($this->newToken($this->provider)));

                return true;

            }, throw: false)
            ->post("https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version=2021-03-01", $body)
            ->onError(fn($exception) => throw new ResourceGraphException($exception))
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

        if (isset($this->take)) {
            $options[] = $this->take;
        }

        if (isset($this->withSubscription)) {
            $options[] = $this->withSubscription;
        }

        array_map(fn($key) => " {$key} ", $options);

        $where = $options ? sprintf("| where %s", implode(' | ', $options)) : null;

        return "resources {$where}";
    }

    /**
     * @throws ResourceGraphException
     */
    private function addToCache(string $hash, array $results): array
    {
        foreach (data_get($results, 'data') as $value) {
            if (!Arr::has($value, ['key', 'value'])) {
                throw new ResourceGraphException('Caching requires "key" and "value"!. Use `project(\'key\', \'value\') for caching.');
            }

            $field = (string)$value['key'];
            $value = (string)$value['value'];

            $cached[] = Redis::hSet($hash, $field, $value);
        }
        return $cached ?? [];
    }
}