<?php

namespace App\Services\AzureAD;

use App\Jobs\DismissRiskyUsersJob;
use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserRiskState
{
    use DeveloperNotification, Token;

    const PROVIDER = 'lhg_graph';

    protected string $userId;

    protected string $url;

    protected string|array $filter;

    protected ?array $properties = null;

    public function __construct()
    {
    }

    public function list()
    {
        return $this->callGraphAPI();
    }

    public function select(RiskyUserProperties $properties): static
    {
        $properties = $properties->get();

        $this->properties[] = $properties ? '$select='.$properties : null;

        return $this;
    }

    public function top(RiskyUserTop $top): static
    {
        $properties = $top->get();

        $this->properties[] = $properties ? '$top='.$properties : null;

        return $this;
    }

    protected function callGraphAPI()
    {
        $url = $this->queryBuilder();

        return Http::withToken(decrypt($this->token(self::PROVIDER)))
            ->retry(5, 50, function ($exception, $request) {
                if ($exception instanceof RequestException && $exception->getCode() >= 402 && $exception->getCode() != 429) {
                    return false;
                }
                if ($exception instanceof RequestException and $exception->getCode() === 429) {
                    sleep($exception->response->header('Retry-After') ?? 10);

                    return true;
                }
                if ($exception instanceof RequestException and $exception->getCode() === 400) {
                    return false;
                }
                $request->withToken(decrypt($this->newToken(self::PROVIDER)));

                return true;
            }, false)
            ->get($url)
            ->onError(
                function (Response $response) {
                    Log::error('RiskyUsers API Error', [
                        'service' => 'risky-users',
                        'message' => $response->reason(),
                        'status' => $response->status(),
                    ]);

                    return [];
                })
            ->json();
    }

    protected function queryBuilder(): string
    {
        $url = 'https://graph.microsoft.com/v1.0/identityProtection/riskyUsers';
        $query = [];
        if (isset($this->userId)) {
            $query[] = $this->userId;
        }
        if (isset($this->properties)) {
            $query[] = implode('&', $this->properties);
        }
        if (isset($this->filter)) {
            $query[] = $this->filter;
        }

        $query = implode('&', $query);
        if (! empty($query)) {
            $url = sprintf('%s?%s', $url, $query);
        }

        return $url;
    }

    public function atRisk($operator = 'and'): static
    {
        $this->filter[] = "riskState eq 'atRisk' and isDeleted eq false and isProcessing eq false";

        $this->filter = '$filter='.implode(' and ', $this->filter);

        return $this;
    }

    public function dismiss(): void
    {
        $userIds = (array) data_get($this->list(), 'value.*.id');

        if (empty($userIds)) {
            Log::info('No RiskyUsers to dismiss', [
                'service' => 'risky-users',
            ]);

            return;
        }

        foreach (array_chunk($userIds, config('services.azure-ad.chunk-dismiss-risky-users')) as $userIds) {
            $jobs[] = new DismissRiskyUsersJob($userIds);
        }

        if (isset($jobs)) {
            Bus::batch($jobs)
                ->name('dismiss-risky-users')
                ->allowFailures()
                ->dispatch();
        }
    }
}
