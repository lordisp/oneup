<?php

namespace App\Jobs\Pdns;

use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token, DeveloperNotification;

    public int $tries = 10;

    protected Response $response;

    protected array $request = [];

    protected bool $withSubscription = false;

    public function __construct(
        protected array  $record,
        protected string $uri,
        protected string $hub,
        protected string $spoke,
        protected string $message,
    )
    {
    }

    public function handle(): void
    {
        if (!$this->recordHasResource($this->record)) {
            return;
        }

        $this->getEtagFromHubOrCreateNewRequest($this->uri, $this->record);

        $this->updateRecord();

        $this->auditResponse();
    }

    protected function getEtagFromHubOrCreateNewRequest($uri, $spokeRecord): void
    {
        $hubRecord = Http::withToken(decrypt($this->token($this->hub)))
            ->retry(20, 0, function ($exception, $request): bool {
                if ($exception instanceof RequestException && $exception->getCode() === 404) {
                    return false;
                }
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->token($this->hub)));
                return true;
            }, throw: false)
            ->get($uri);

        if ($hubRecord->failed()) {
            return;
        }

        $this->request = Arr::exists($hubRecord, 'code')
            ? ['properties' => $spokeRecord['properties'], 'headers' => ['If-None-Match' => '*']]
            : ['properties' => $spokeRecord['properties'], 'headers' => $this->skipIfEqual($hubRecord->json(), $spokeRecord), 'etag' => $hubRecord->json()['etag'],];
    }

    protected function skipIfEqual($hubRecord, $spokeRecord): array
    {
        $hubRecord = $this->normalizeRecord($hubRecord);
        $spokeRecord = $this->normalizeRecord($spokeRecord);

        if (json_encode($hubRecord['properties']) == json_encode($spokeRecord['properties'])) {
            return ['skip' => true];
        }

        return ['If-Match' => $hubRecord['etag']];
    }

    protected function updateRecord(): void
    {
        if (data_get($this->request, 'headers.skip')) {
            return;
        }
        $this->response = Http::withToken(decrypt($this->token($this->hub)))
            ->retry(10, 200, function ($exception, $request) {
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->newToken($this->hub)));
                return true;
            }, throw: false)
            ->put($this->uri, Arr::only($this->request, ['etag', 'properties']));
    }

    protected function auditResponse()
    {
        if (!isset($this->response)) {
            return;
        }
        if ($this->response->failed()) {
            Log::warning('Patch Failed' . json_encode($this->response->json()), $this->response->json());
            return;
        }
        Log::info($this->message, $this->record['properties']);
    }

    private function recordHasResource(array $record): bool
    {
        $subscriptionId = explode('/', $record['id'])[2];

        $withSubscription = $this->withSubscription
            ? " and subscriptionId == '{$subscriptionId}'"
            : null;

        $query = "resources | where name has '{$record['name']}'{$withSubscription}";

        $apiVersion = '2021-03-01';

        $uri = "https://management.azure.com/providers/Microsoft.ResourceGraph/resources?api-version={$apiVersion}";

        $response = cache()->remember(md5($query), now()->addMinutes(30), function () use ($query, $uri) {
            return Http::withToken(decrypt($this->token($this->spoke)))
                ->retry(10, 200, function ($exception, $request) {
                    if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                        return true;
                    }

                    $request->withToken(decrypt($this->newToken($this->hub)));

                    return true;

                }, throw: false)
                ->post($uri, ['query' => $query]);
        });

        if ($response->failed()) {
            return false;
        }

        return $response->json('totalRecords') > 0;
    }

    protected function normalizeRecord($record)
    {
        Arr::forget($record['properties'], ['metadata', 'ttl', 'isAutoRegistered']);
        return $record;
    }

    public function fail($exception = null)
    {
        $this->sendDeveloperNotification($exception);
    }
}
