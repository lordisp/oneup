<?php

namespace App\Services\ServiceNow;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
        $this->validateGroupNames($groupNames);

        $this->businessServiceNames = (array) $businessServiceNames;
    }

    public function handle(): array
    {
        $this->trimBusinessServiceNames();

        return $this->getGroupMembers();
    }

    private function generateCacheKey(): string
    {
        return implode('_', $this->groupNames).'_'.implode('_', $this->businessServiceNames);
    }

    private function logError(RequestException $exception): array
    {
        Log::error('Error while calling ServiceNow API', [
            'message' => $exception->getMessage(),
            'businessServiceNames' => $this->businessServiceNames,
            'groupNames' => $this->groupNames,
            'status' => $exception->response->status(),
            'trigger' => 'GroupMembers',
            'api' => 'ServiceNow',
        ]);

        return [];
    }

    private function callServiceNowApi(): array
    {
        $body = [
            'names' => $this->businessServiceNames,
            'types' => $this->groupNames,
        ];
        $results = Http::withBasicAuth(config('servicenow.client_id'), config('servicenow.client_secret'))
            ->retry(5, 50, function ($exception) {
                if ($exception instanceof RequestException and $exception->getCode() === 429) {
                    $retryAfter = $exception->response->header('Retry-After');
                    sleep(empty($retryAfter) ? 10 : (int) $retryAfter);

                    return true;
                }
                if ($exception instanceof RequestException and $exception->getCode() === 400) {
                    return false;
                }

                return $exception instanceof ConnectionException;
            })
            ->post(config('servicenow.uri').'/api/delag/retrieve_cost_centers/GetGroupFromBsandType', $body)
            ->json();

        return $this->trimResults($results);
    }

    private function trimResults(array $results): array
    {
        return array_unique(
            array_map('mb_strtolower', Arr::flatten(
                $results
            ))
        );
    }

    private function trimBusinessServiceNames(): void
    {
        foreach ($this->businessServiceNames as $key => $name) {
            if (Str::contains($name, '[non-operational]', true)) {
                $this->trimAndRemoveServiceName($key, $name, '[non-operational]');
            }
            if (Str::contains($name, '_Damaged', true)) {
                $this->trimAndRemoveServiceName($key, $name, '_Damaged');
            }
        }
    }

    private function trimAndRemoveServiceName($key, $name, $subString): void
    {
        $name = Str::remove($subString, $name, false);
        $this->businessServiceNames[] = trim($name);
        unset($this->businessServiceNames[$key]);
        sort($this->businessServiceNames);
    }

    private function validateGroupNames(string|array $groupNames): void
    {
        $allowedGroups = [
            'EscalationNotification',
            'Responsibles',
            'SecurityContacts',
        ];

        $groupNamesArr = (array) $groupNames;

        foreach ($groupNamesArr as $groupName) {
            if (! in_array($groupName, $allowedGroups)) {
                throw new \InvalidArgumentException(sprintf('Group name %s is not allowed.', $groupName));
            }
        }

        $this->groupNames = $groupNamesArr;
    }

    private function getGroupMembers(): array
    {
        $key = $this->generateCacheKey();

        return cache()->remember(Str::lower($key), $this->ttl, function () {
            try {
                return $this->callServiceNowApi();
            } catch (RequestException $exception) {
                return $this->logError($exception);
            }
        });
    }
}
