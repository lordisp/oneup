<?php

namespace App\Jobs\Pdns;

use App\Exceptions\UpdateRecordJobException;
use App\Jobs\RecordHasResourceJob;
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
use Throwable;

class UpdateRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token, DeveloperNotification;

    public int $tries = 10;

    protected Response $response;

    protected array $request = [];

    protected bool $withSubscription = false;

    public function __construct(protected array $attributes)
    {
    }

    /**
     * @throws UpdateRecordJobException
     */
    public function handle(): void
    {
        if (!$this->recordHasResource($this->attributes['record'])) {
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
                if ($exception instanceof RequestException && $exception->getCode() === 404) {
                    return false;
                }
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->newToken($this->attributes['hub'])));
                return true;
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

    protected function updateRecord(): void
    {
        if (data_get($this->request, 'headers.skip')) {
            return;
        }
        $this->response = Http::withToken(decrypt($this->attributes['token']))
            ->retry(10, 200, function ($exception, $request) {
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->newToken($this->attributes['hub'])));
                return true;
            }, throw: false)
            ->put($this->attributes['uri'], Arr::only($this->request, ['etag', 'properties']));
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
        Log::info($this->attributes['message'], $this->attributes['record']['properties']);
    }

    /**
     * @throws UpdateRecordJobException
     */
    private function recordHasResource(array $record): bool
    {
        if ($this->skipZoneForValidation($record)) {
            return true;
        }

        $resources = data_get($this->attributes, 'resources');

        if (empty($resources)) {
            throw new UpdateRecordJobException('No resources in cache!');
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
}
