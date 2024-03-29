<?php

namespace App\Jobs\Pdns;

use App\Exceptions\UpdateRecordJobException;
use App\Facades\AzureArm\ResourceGraph;
use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Batchable;
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
use Throwable;

class UpdateRecordJob implements ShouldQueue
{
    use Batchable, DeveloperNotification, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token;

    public int $tries = 10;

    public int $backoff = 5;

    protected Response $response;

    protected array $request = [];

    protected bool $withSubscription = false;

    public function __construct(protected array $attributes = [])
    {
    }

    public function uniqueId(): string
    {
        return md5($this->attributes['record']['name'].$this->attributes['spoke']);
    }

    /**
     * @throws UpdateRecordJobException
     */
    public function handle(): void
    {
        if (! $this->recordHasResource($this->attributes['record'])) {
            Log::debug('No resource found for '.$this->attributes['record']['name'], [
                'Trigger' => 'UpdateRecordJob',
                'batch' => $this->batchId,
                'spoke' => $this->attributes['spoke'],
                'properties' => $this->attributes['record']['properties'],
            ]);

            return;
        }

        $this->getEtagFromHubOrCreateNewRequest($this->attributes['uri'], $this->attributes['record']);

        $this->updateRecord();

        $this->auditResponse();
    }

    protected function getEtagFromHubOrCreateNewRequest($uri, $spokeRecord): void
    {
        $hubRecord = Http::withToken(decrypt($this->attributes['token']))
            ->retry(20, 0, function ($exception, $request): bool {
                return $this->requestExceptions($exception, $request);
            }, throw: false)
            ->get($uri);

        if ($hubRecord->failed()) {
            return;
        }

        $this->request = Arr::exists($hubRecord, 'code')
            ? ['properties' => $spokeRecord['properties'], 'headers' => ['If-None-Match' => '*']]
            : ['properties' => $spokeRecord['properties'], 'headers' => $this->skipIfEqual($hubRecord->json(), $spokeRecord), 'etag' => $hubRecord->json()['etag']];

        unset($hubRecord, $spokeRecord);
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

    /**
     * @description Update the record in the hub
     *
     * @see https://learn.microsoft.com/en-us/rest/api/dns/privatedns/record-sets/create-or-update?view=rest-dns-privatedns-2018-09-01&tabs=HTTP
     */
    protected function updateRecord(): void
    {
        if (data_get($this->request, 'headers.skip')) {
            return;
        }

        Log::debug('Updating '.$this->attributes['record']['name'], [
            'Trigger' => 'UpdateRecordJob',
            'batch' => $this->batchId,
            'spoke' => $this->attributes['spoke'],
            'properties' => $this->attributes['record']['properties'],
        ]);

        $this->response = Http::withToken(decrypt($this->attributes['token']))
            ->retry(10, 200, function ($exception, $request) {
                return $this->requestExceptions($exception, $request);
            }, throw: false)
            ->put($this->attributes['uri'], Arr::only($this->request, ['etag', 'properties']));
    }

    protected function auditResponse()
    {
        if (! isset($this->response)) {
            return;
        }

        if ($this->response->failed()) {
            Log::warning('Patching '.$this->attributes['record']['name'].' Failed: '.$this->response->reason(), $this->response->json());

            return;
        }

        Log::info($this->attributes['message'], [
            'batch' => $this->batchId,
            'spoke' => $this->attributes['spoke'],
            'properties' => $this->attributes['record']['properties'],
        ]);
    }

    /**
     * @throws UpdateRecordJobException
     */
    private function recordHasResource(array $record): bool
    {
        if ($this->skipZoneForValidation($record)) {
            return true;
        }

        if (in_array($this->attributes['spoke'], array_map('trim', explode(',', config('dnssync.skip_providers'))))) {
            return true;
        }

        $resources = ResourceGraph::fromCache("networkinterfaces:{$this->attributes['spoke']}");

        if (empty($resources)) {
            throw new UpdateRecordJobException('No resources in cache for '.$this->attributes['spoke']);
        }

        return (new RecordHasResourceJob(
            basename($record['type']),
            $record,
            $resources)
        )->handle();
    }

    protected function normalizeRecord($record)
    {
        Arr::forget($record['properties'], ['metadata', 'isAutoRegistered']);

        return $record;
    }

    public function failed(Throwable $exception)
    {
        $this->sendDeveloperNotification($exception);
    }

    protected function skipZoneForValidation(array $record): bool
    {
        preg_match('/privatelink\.([a-zA-Z0-9.-]+)/', $record['id'], $match);

        return in_array($match[0], $this->attributes['skippedZonesForValidation']);
    }

    /**
     * @return true
     */
    private function requestExceptions($exception, $request): bool
    {
        if ($exception instanceof RequestException && $exception->getCode() === 429) {
            $retryAfter = $exception->response->header('Retry-After');
            sleep(empty($retryAfter) ? 10 : $retryAfter);

            return true;
        }

        if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
            return true;
        }
        $request->withToken(decrypt($this->newToken($this->attributes['hub'])));

        return true;
    }
}
