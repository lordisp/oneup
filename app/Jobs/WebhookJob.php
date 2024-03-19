<?php

namespace App\Jobs;

use App\Jobs\Webhook\AlertsChangeStateJob;
use App\Traits\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token;

    protected array $jobs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected $data)
    {
        $this->jobs = config('webhook');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->jobs as $key => $job) {
            if ($this->validateJob($job, $key, $this->data)) {
                $job::dispatch($this->data)->onQueue('webhook');
            }
        }
    }

    protected function validateJob($job, $key, $data): bool
    {
        return $key === data_get($data, 'essentials.alertRule') && class_exists($job);
    }

    protected function updateState($state, $scope, $comment)
    {
        AlertsChangeStateJob::dispatch(
            data_get($this->data, 'essentials.alertId'),
            $state,
            $scope,
            $comment
        )->onQueue('webhook');
    }

    protected function getMembers(): array
    {
        $token = $this->token($this->armProvider);
        $linkToSearchResultsAPI = Arr::first(data_get($this->data, 'alertContext.condition.allOf.*.linkToSearchResultsAPI'));
        $tables = Http::withToken(decrypt($token))
            ->retry(20, 200, function ($exception, $request) {
                if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
                    return false;
                }
                $request->withToken($this->token($this->armProvider));

                return true;
            }, throw: false)
            ->get($linkToSearchResultsAPI)->json('tables');

        return $this->getUsersById(! empty($tables) ? $this->members($tables, $this->rowKey($tables)) : []);
    }

    protected function rowKey($tables)
    {
        $values = Arr::first(data_get($tables, '*.columns'));
        foreach ($values as $key => $value) {
            if ($value['name'] === 'TargetResources') {
                return $key;
            }
        }
    }

    protected function members($tables, $rowKey): array
    {
        $rows = Arr::first(data_get($tables, '*.rows'));
        $member = [];
        foreach ($rows as $row) {
            $member[] = data_get(json_decode($row[$rowKey], true), '*.id');
        }

        return array_unique(Arr::flatten($member));
    }

    protected function getUsersById($memberIds): array
    {
        $responses = Http::pool(function (Pool $pool) use ($memberIds) {
            $token = $this->token($this->graphProvider);
            foreach ($memberIds as $memberId) {
                $pool->withToken(decrypt($token))
                    ->retry(20, 200, function ($exception, $request) {
                        if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
                            return false;
                        }
                        $request->withToken($this->token($this->graphProvider));

                        return true;
                    }, throw: false)
                    ->get('https://graph.microsoft.com/v1.0/users/'.$memberId.'?$select=id,displayName,givenName,surname,mail,userPrincipalName');
            }
        });
        $members = [];
        foreach ($responses as $response) {
            if ($response->successful()) {
                $members[] = $response->json();
            } elseif ($response->failed()) {
                Log::error('WebhookJob getUsersById error: '.data_get($response->json('error'), 'message'));
            }
        }

        return $members;
    }
}
