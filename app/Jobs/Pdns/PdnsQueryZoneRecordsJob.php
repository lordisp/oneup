<?php

namespace App\Jobs\Pdns;

use App\Traits\DeveloperNotification;
use App\Traits\Token;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Validator;

class PdnsQueryZoneRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DeveloperNotification;

    use Token;

    public int $timeout = 300;

    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = Validator::validate($attributes, [
            'zone' => 'required',
            'hub' => 'required',
            'spoke' => 'required',
            'recordType' => 'required',
            'subscriptionId' => 'required',
            'resourceGroup' => 'required',
            'resources' => 'required',
            'skippedZonesForValidation' => 'array',
        ]);

        $this->attributes['token'] = $this->token($attributes['hub']);
    }

    public function handle(): void
    {
        $records = $this->getRecords();

        $zoneName = basename($this->attributes['zone']);

        $spokeSubscriptionId = $this->spokeSubscriptionId();

        Log::debug("Updating {$zoneName} from {$spokeSubscriptionId}");

        foreach ($records as $record) {

            if ($this->isRecordType($record)) {

                $type = basename($record['type']);

                $this->attributes['uri'] = 'https://management.azure.com/subscriptions/' . $this->attributes['subscriptionId'] . '/resourceGroups/' . $this->attributes['resourceGroup'] . '/providers/Microsoft.Network/privateDnsZones/' . $zoneName . '/' . $type . '/' . $record['name'] . '?api-version=2018-09-01';

                $this->attributes['message'] = "Update {$type} record {$record['name']} from {$spokeSubscriptionId} to {$this->attributes['subscriptionId']}";

                $this->attributes['record'] = $record;

                UpdateRecordJob::dispatch($this->attributes);
            }
        }
    }

    protected function spokeSubscriptionId(): string
    {
        return explode('/', $this->attributes['zone'])[2];
    }

    protected function getRecords(): array
    {
        return Http::withToken(decrypt($this->token($this->attributes['spoke'])))
            ->retry(100, 200, function ($exception, $request) {
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->token($this->attributes['spoke'])));
                return true;
            }, throw: false)
            ->get('https://management.azure.com' . $this->attributes['zone'] . '/ALL?api-version=2018-09-01&$top=1000')
            ->onError(fn() => [])
            ->json('value');
    }

    protected function isRecordType($record): bool
    {
        return in_array(basename(data_get($record, 'type')), $this->attributes['recordType']);
    }

    public function failed($exception = null)
    {
        $this->sendDeveloperNotification($exception);
    }
}
