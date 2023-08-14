<?php

namespace App\Services\AzureAD;

use App\Facades\TokenCache;
use App\Traits\DeveloperNotification;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserRiskState
{
    use DeveloperNotification;
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
        $this->url = '/identityProtection/riskyUsers';

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

        return Http::withToken($this->getAccessToken())
            ->retry(5, 50, function ($exception, $request) {
                if ($exception instanceof RequestException && $exception->getCode() === 404) {
                    return false;
                }
                $request->withToken($this->getAccessToken());
                return true;
            }, false)
            ->get($url)
            ->onError(/**
             * @throws RequestException
             */ fn($response) => throw new RequestException($response))
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

    protected function getAccessToken(): string
    {
        return decrypt(
            TokenCache::provider(self::PROVIDER)
                ->get()
        );

    }

    public function atRisk($operator = 'and'): static
    {
        $this->filter[] = "riskState eq 'atRisk'";

        $this->filter = "\$filter=" . implode(' and ', $this->filter);

        return $this;
    }


    public function dismiss(): \Illuminate\Http\Response|\Illuminate\Http\Client\Response
    {
        $values = array_filter(data_get($this->list(), 'value'), fn($item) => data_get($item,'isDeleted') === false);

        $values = data_get($values, '*.id');

        if (empty($values)) {
            Log::info('No RiskyUsers to dismiss');

            return Response('Nothing to process', 200);
        }

        $response = Http::withToken($this->getAccessToken())
            ->retry(5, 50, function ($exception, $request) {
                if ($exception instanceof RequestException && $exception->getCode() === 500) {

                    Log::error('Dismiss RiskyUser failed',(array)$exception);

                    $this->sendDeveloperNotification($exception);

                    return false;
                }
                $request->withToken($this->getAccessToken());
                return true;
            }, false)
            ->post('https://graph.microsoft.com/v1.0/identityProtection/riskyUsers/dismiss', [
                'userIds' => $values
            ]);

        if ($response->status() === 204) {
            Log::info('Dismissed RiskyUsers', $values);
            return $response;
        }

        Log::error($response->reason());
        return $response;
    }
}