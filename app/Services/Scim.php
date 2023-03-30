<?php

namespace App\Services;

use App\Facades\AzureAD\User as AzureADUser;
use App\Jobs\Scim\ImportUserJob;
use App\Models\BusinessService;
use App\Models\TokenCacheProvider;
use App\Models\User;
use App\Services\AzureAD\UserPrincipal;
use App\Services\AzureAD\UserProperties;
use App\Traits\Token;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Str;

class Scim
{
    use Token;

    protected string $provider;
    protected bool $status;
    protected array $users = [];
    protected string $businessService = '';

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
                ->retry(10, 50, function ($exception, $request): bool {
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

    public function withBusinessService($businessService): static
    {
        $this->businessService = $businessService;
        return $this;
    }

    public function users(string|array $userPrincipalNames): static
    {
        $userPrincipalNames = (array)$userPrincipalNames;
        $users = [];

        foreach ($userPrincipalNames as $userPrincipalName) {

            $user = AzureADUser::select(new UserProperties('id,displayName,givenName,surname'))
                ->get(new UserPrincipal($userPrincipalName));

            if (!Arr::has($user, 'error')) {
                $user['email'] = $userPrincipalName;
                $user['displayName'] = Str::title($user['displayName']);
                $user['givenName'] = Str::title($user['givenName']);
                $user['surname'] = Str::title($user['surname']);
                $users[] = $user;
            }
        }

        $this->users = $users;
        return $this;

    }

    public function add(): Response
    {
        $this->status = true;
        $this->updateOrCreate($this->users);
        return response(status: 201);
    }

    public function remove(): void
    {
        $this->status = false;
        $this->updateOrCreate($this->users);
    }


    protected function updateOrCreate(array $users): void
    {
        foreach ($users as $user) {
            $userInstance = $this->saveUserInstance($user);
            if (isset($this->businessService)) {
                $businessService = BusinessService::firstOrCreate(['name' => $this->businessService]);
                try {
                    $userInstance->businessServices()->syncWithoutDetaching($businessService->id);
                } catch (Exception) {
                    break;
                }
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

    public function saveUserInstance(array $user): User
    {
        try {
            return User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'displayName' => $user['displayName'],
                    'firstName' => $user['givenName'],
                    'lastName' => $user['surname'],
                    'provider_id' => $user['id'],
                    'status' => $this->status,
                    'provider' => $this->provider,
                ]);
        } catch (QueryException $exception) {
            Log::error("Scim: Failed to updateOrCreate {$user['email']}", (array)$exception);
            return User::newModelInstance();
        }
    }

}