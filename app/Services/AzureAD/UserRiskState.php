<?php

namespace App\Services\AzureAD;

use App\Jobs\DismissRiskyUsersJob;
use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Batch;
use Illuminate\Http\Client\RequestException;
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
    protected array|null $properties = null;

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

        $this->properties[] = $properties ? "\$select=" . $properties : null;

        return $this;
    }

    public function top(RiskyUserTop $top): static
    {
        $properties = $top->get();

        $this->properties[] = $properties ? "\$top=" . $properties : null;

        return $this;
    }

    protected function callGraphAPI()
    {
        $url = $this->queryBuilder();

        return Http::withToken(decrypt($this->token(self::PROVIDER)))
            ->retry(5, 50, function ($exception, $request) {
                if ($exception instanceof RequestException && $exception->getCode() >= 402) {
                    return false;
                }
                $request->withToken(decrypt($this->newToken(self::PROVIDER)));
                return true;
            }, false)
            ->get($url)
            ->onError(
                fn($response) => Log::error('RiskyUsers API Error', (array)$response))
            ->json();
    }


    protected function queryBuilder(): string
    {
        $url = "https://graph.microsoft.com/v1.0/identityProtection/riskyUsers";
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
        if (!empty($query)) {
            $url = sprintf("%s?%s", $url, $query);
        }
        return $url;
    }

    public function atRisk($operator = 'and'): static
    {
        $this->filter[] = "riskState eq 'atRisk'";

        $this->filter = "\$filter=" . implode(' and ', $this->filter);

        return $this;
    }


    public function dismiss(): Batch|null
    {
        $values = array_filter(
            (array)data_get($this->list(), 'value'), fn($item) => data_get($item, 'isDeleted') === false
        );

        $userIds = data_get($values, '*.id');

        if (empty($userIds)) {
            Log::info('No RiskyUsers to dismiss');
            return null;
        }

        $jobs = [];
        foreach (array_chunk($userIds, 79) as $userIds) {
            $jobs[] = new DismissRiskyUsersJob($userIds);
        }

        return Bus::batch($jobs)
            ->name('dismiss-risky-users')
            ->dispatch();
    }
}