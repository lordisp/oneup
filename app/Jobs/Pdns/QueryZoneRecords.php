<?php

namespace App\Jobs\Pdns;

use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QueryZoneRecords implements ShouldQueue
{
    use Batchable, DeveloperNotification, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Token;

    protected array $attributes;

    public int $tries = 10;

    public int $backoff = 3;

    public function __construct(array $attributes)
    {
        $this->attributes = Validator::validate($attributes, [
            'zone' => 'required',
            'hub' => 'required',
            'spoke' => 'required',
            'recordType' => 'required',
            'subscriptionId' => 'required',
            'resourceGroup' => 'required',
            'skippedZonesForValidation' => 'array',
        ]);

        $this->attributes['token'] = $this->token($attributes['hub']);
        $this->attributes['spoke_token'] = $this->token($attributes['spoke']);
    }

    public function handle(): void
    {
        $records = $this->getRecords();

        $zoneName = basename($this->attributes['zone']);

        $spokeSubscriptionId = $this->spokeSubscriptionId();

        $jobs = [];

        foreach ($records as $record) {

            if (data_get($record, 'name') === '*') {
                Log::info('Skipping wildcard record', [
                    'Trigger' => 'PdnsQueryZoneRecordsJob',
                    'Resource' => $this->attributes['zone'],
                    'record' => $record,
                ]);

                return;
            }
            if ($this->isRecordType($record)) {

                $type = basename($record['type']);

                $this->attributes['uri'] = 'https://management.azure.com/subscriptions/'.$this->attributes['subscriptionId'].'/resourceGroups/'.$this->attributes['resourceGroup'].'/providers/Microsoft.Network/privateDnsZones/'.$zoneName.'/'.$type.'/'.$record['name'].'?api-version=2018-09-01';

                $this->attributes['message'] = "Update {$type} record {$record['name']} from {$spokeSubscriptionId} to {$this->attributes['subscriptionId']}";

                $this->attributes['record'] = $record;

                $jobs[] = new UpdateRecordJob($this->attributes);
            } else {
                if (basename($record['type']) != 'SOA') {
                    Log::info(sprintf('Skipping record from spoke %s', $this->attributes['spoke']), [
                        'Trigger' => 'PdnsQueryZoneRecordsJob',
                        'Resource' => $this->attributes['zone'],
                        'Record' => $record,
                    ]);
                }

            }
        }

        if (count($jobs) > config('services.pdns.chunk.records')) {
            $jobs = array_chunk($jobs, config('services.pdns.chunk.records'));
        }

        if (count($jobs) > 0) {
            Bus::batch($jobs)
                ->onQueue(config('dnssync.queue_name'))
                ->name('records')
                ->dispatch();

            Log::info(sprintf('Updating %s records for %s', count($jobs), $this->attributes['zone']), [
                'Trigger' => 'PdnsQueryZoneRecordsJob',
                'Resource' => $this->attributes['zone'],
                'Jobs' => count($jobs),
                'Records' => $records,
            ]);

            return;
        }

        Log::info(sprintf('No records found for %s', basename($this->attributes['zone'])), [
            'Trigger' => 'PdnsQueryZoneRecordsJob',
            'Resource' => $this->attributes['zone'],
        ]);
    }

    protected function spokeSubscriptionId(): string
    {
        return explode('/', $this->attributes['zone'])[2];
    }

    protected function getRecords(): array
    {
        return (array) Http::withToken(decrypt($this->attributes['spoke_token']))
            ->retry(100, 10, function ($exception, $request) {
                if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->token($this->attributes['spoke'])));

                return true;
            }, throw: false)
            ->get('https://management.azure.com'.$this->attributes['zone'].'/ALL?api-version=2018-09-01&$top=1000')
            ->onError(fn () => [])
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
