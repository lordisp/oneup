<?php

namespace App\Services;

use App\Jobs\Scim\ImportUserJob;
use App\Models\TokenCacheProvider;
use App\Models\User;
use App\Traits\Token;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Scim
{
    use Token;

    protected string $provider;
    protected bool $status;
    protected array $users = [];

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
                ->withToken(decrypt($this->token($this->provider)))
                ->retry(10, 200, function ($exception, $request): bool {
                    if ($exception instanceof RequestException && $exception->getCode() === 401) {
                        Log::warning('Scim: Group-Members: ' . $exception->getMessage());
                        $request->withToken(decrypt($this->token($this->provider)));
                        return true;
                    } elseif ($exception instanceof RequestException && $exception->getCode() === 404) {
                        Log::warning('Scim: Group-Members: ' . $exception->getMessage());
                        return false;
                    } else {
                        Log::error('Scim: Group-Members: ' . $exception->getMessage());
                        $request->withToken(decrypt($this->token($this->provider)));
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

    public function users(string|array $userPrincipalNames): static
    {
        $userPrincipalNames = (array)$userPrincipalNames;
        $users = collect();
        foreach ($userPrincipalNames as $userPrincipalName) {

            $token = decrypt($this->token($this->provider));

            $user = Http::withToken($token)
                ->retry(10, 0, function ($exception, $request) use ($userPrincipalName): bool {
                    if ($exception instanceof RequestException && $exception->getCode() === 404) {
                        #todo notify administrators
                        return false;
                    } else {
                        Log::error('AddUserFromAADJob/GetUser: ' . $exception->getMessage());
                        $request->withToken(decrypt($this->token($this->provider)));
                        return true;
                    }
                }, throw: false)
                ->get("https://graph.microsoft.com/v1.0/users/{$userPrincipalName}?\$select=id,displayName,givenName,surname")
                ->json();

            if (!Arr::has($user, 'error')) {
                unset($user['@odata.context']);
                $user['email'] = $userPrincipalName;

                $users = $users->add($user);
            }
        }

        $this->users = $users->toArray();
        return $this;

    }

    public function add(): void
    {
        $this->status = true;
        $this->updateOrCreate($this->users);
    }

    public function remove(): void
    {
        $this->status = false;
        $this->updateOrCreate($this->users);
    }


    protected function updateOrCreate($users): void
    {
        foreach ($users as $user) {
            try {
                User::updateOrCreate(
                    ['provider_id' => $user['id']],
                    [
                        'provider_id' => $user['id'],
                        'displayName' => $user['displayName'],
                        'firstName' => $user['givenName'],
                        'lastName' => $user['surname'],
                        'email' => $user['email'],
                        'status' => $this->status
                    ]);
            } catch (QueryException $exception) {
                Log::error("Scim: Failed to updateOrCreate {$user['email']}", (array)$exception);
            }

        }
    }

    protected function importUserAccounts($members): void
    {
        foreach ($members as $member) {
            ImportUserJob::dispatch($member, $this->provider)->onQueue('admin');
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