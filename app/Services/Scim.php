<?php

namespace App\Services;

use App\Jobs\Scim\ImportUser;
use App\Models\TokenCacheProvider;
use App\Traits\Token;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Scim
{
    use Token;

    protected string $provider;

    public function provider($provider): static
    {
        $this->provider = $this->preValidateProvider($provider)['provider'];
        return $this;
    }

    public function groups(string|array $objectIds): static
    {
        $objectIds = (array)$objectIds;
        foreach ($objectIds as $objectId) {
            $groupMembersUrl = 'https://graph.microsoft.com/v1.0/groups/' . $objectId . '/members/microsoft.graph.user?$count=true&$select=id,displayName,givenName,surname,mail,userPrincipalName';
            $response = Http::withHeaders(['ConsistencyLevel' => 'eventual'])
                ->withToken($this->token($this->provider))
                ->retry(10, 200, function ($exception, $request): bool {
                    if ($exception instanceof RequestException && $exception->getCode() === 401) {
                        Log::warning('Scim: Group-Members: ' . $exception->getMessage());
                        $request->withToken($this->token($this->provider));
                        return true;
                    } elseif ($exception instanceof RequestException && $exception->getCode() === 404) {
                        Log::warning('Scim: Group-Members: ' . $exception->getMessage());
                        return false;
                    } else {
                        Log::error('Scim: Group-Members: ' . $exception->getMessage());
                        $request->withToken($this->token($this->provider));
                        return true;
                    }
                }, throw: false)
                ->get($groupMembersUrl);
            if ($response->successful()) {
                $members = $response->json('value');
                $this->importUserAccounts($members);
            }
        }
        return $this;
    }

    protected function importUserAccounts($members): void
    {
        foreach ($members as $member) {
            ImportUser::dispatch($member, $this->provider)->onQueue('admin');
        }
    }

    protected function preValidateProvider($provider): array
    {
        $provider = TokenCacheProvider::where('name', '=', $provider)->first();
        if (!empty($provider)) $provider = $provider->name;
        return Validator::make(['provider' => $provider], [
            'provider' => 'required|string'
        ])->validate();
    }

}