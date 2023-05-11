<?php

namespace App\Jobs\Pdns;

use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PdnsQueryZoneRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DeveloperNotification;

    use Token;

    protected string $token;

    public function __construct(
        protected string $zone,
        protected string $hub,
        protected string $spoke,
        protected array  $recordType,
        protected string $subscriptionId,
        protected string $resourceGroup
    )
    {
        $this->token = $this->token($this->hub);
    }

    public function handle(): void
    {
        $records = $this->getRecords();

        $zoneName = basename($this->zone);

        $spokeSubscriptionId = $this->spokeSubscriptionId();

        Log::debug("Updating {$zoneName} from {$spokeSubscriptionId}");

        foreach ($records as $record) {

            if ($this->isRecordType($record)) {

                $type = basename($record['type']);

                $uri = 'https://management.azure.com/subscriptions/' . $this->subscriptionId . '/resourceGroups/' . $this->resourceGroup . '/providers/Microsoft.Network/privateDnsZones/' . $zoneName . '/' . $type . '/' . $record['name'] . '?api-version=2018-09-01';

                $message = "Update {$type} record {$record['name']} from {$spokeSubscriptionId} to {$this->subscriptionId}";

                UpdateRecordJob::dispatch($this->token, $record, $uri, $this->hub, $this->spoke, $message);
            }
        }
    }

    protected function spokeSubscriptionId(): string
    {
        return explode('/', $this->zone)[2];
    }

    protected function getRecords(): array
    {
        return Http::withToken(decrypt($this->token($this->spoke)))
            ->retry(100, 200, function ($exception, $request) {
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->token($this->spoke)));
                return true;
            }, throw: false)
            ->get('https://management.azure.com' . $this->zone . '/ALL?api-version=2018-09-01&$top=1000')
            ->onError(fn() => [])
            ->json('value');
    }

    protected function isRecordType($record): bool
    {
        return in_array(basename(data_get($record, 'type')), $this->recordType);
    }

    public function fail($exception = null)
    {
        $this->sendDeveloperNotification($exception);
    }
}
