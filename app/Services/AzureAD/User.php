<?php

namespace App\Services\AzureAD;

use App\Facades\TokenCache;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class User
{
    const PROVIDER = 'lhg_graph';

    protected string $userId;

    protected ?string $properties = null;

    public function __construct()
    {
    }

    public function get(UserPrincipal|UserId $principal): array
    {
        $this->userId = $principal->get();

        return $this->callGraphAPI();
    }

    public function select(UserProperties $properties): static
    {
        $properties = $properties->get();
        $this->properties = $properties ? '?$select='.$properties : null;

        return $this;
    }

    protected function callGraphAPI(): array
    {
        return cache()->rememberForever($this->userId, function () {
            return Http::withToken($this->getAccessToken())
                ->retry(5, 50, function ($exception, $request) {
                    if ($exception instanceof RequestException && $exception->getCode() === 404) {
                        return false;
                    }
                    $request->withToken($this->getAccessToken());

                    return true;
                }, false)
                ->get("https://graph.microsoft.com/v1.0/users/{$this->userId}{$this->properties}")
                ->collect()
                ->forget('@odata.context')
                ->all();
        });
    }

    protected function getAccessToken(): string
    {
        return decrypt(TokenCache::provider(self::PROVIDER)->get());

    }
}
