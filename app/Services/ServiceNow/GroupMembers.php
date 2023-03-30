<?php

namespace App\Services\ServiceNow;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GroupMembers
{

    protected string|array $businessServiceNames;
    protected string|array $groupNames;

    public function __construct(string|array $businessServiceNames, string|array $groupNames, protected int $ttl = 3600)
    {
        $this->groupNames = (array)$groupNames;
        $this->businessServiceNames = (array)$businessServiceNames;
    }

    public function handle(): array
    {
        $key = implode('_', $this->groupNames) . '_' . implode('_', $this->businessServiceNames);

        return cache()->remember(Str::lower($key), $this->ttl, function () {
            return $this->callServiceNowApi();
        });
    }

    protected function callServiceNowApi(): array
    {
        $results = Http::withBasicAuth(config('servicenow.client_id'), config('servicenow.client_secret'))
            ->retry(5, 50, function ($exception) {
                return $exception instanceof ConnectionException;
            })
            ->post(config('servicenow.uri') . '/api/delag/retrieve_cost_centers/GetGroupFromBsandType', [
                'names' => $this->businessServiceNames,
                'types' => $this->groupNames,
            ])
            ->json();
        return $this->flattenResults($results);
    }

    protected function flattenResults(array $results):array
    {
        return array_unique(Arr::flatten($results));
    }
}