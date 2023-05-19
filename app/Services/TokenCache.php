<?php

namespace App\Services;

use App\Models\TokenCacheProvider;
use Arr;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TokenCache
{
    protected string $token, $provider;

    protected array $config = [], $client = [];

    public bool $cache = true;

    public function __construct()
    {
        $this->config = $this->loadConfig();
    }

    public function get(): string
    {
        $this->getToken();
        return $this->token;
    }

    public function provider($provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function withoutEncryption(): static
    {
        $this->config['encrypt'] = false;
        return $this;
    }

    public function authCode(): RedirectResponse
    {
        $url = sprintf(
            "https://login.microsoftonline.com/%s%s?",
            data_get($this->config[$this->provider], 'client.tenant'),
            data_get($this->config[$this->provider], 'auth_url')
        );

        $state = hash('sha256', now());

        $params = [
            'client_id' => data_get($this->config[$this->provider], 'client.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('app.url') . '/callback',
            'response_mode' => 'query',
            'prompt' => 'select_account',
            'scope' => 'offline_access email openid profile https://graph.microsoft.com/.default',
            'state' => $state,
            'code_challenge' => $state,
        ];

        session(['authState' => $state]);

        $url .= Arr::query($params);

        return $url;
    }

    public function accessToken(array $params = []): string|RedirectResponse
    {
        $now = time() + 300;
        $tenant = data_get($this->config[$this->provider], 'client.tenant');
        $clientId = data_get($this->config[$this->provider], 'client.client_id');
        $secret = decrypt(data_get($this->config[$this->provider], 'client.client_secret'));

        if (auth()->check()) {
            $oid = auth()->user()->provider_id;
            $token = Cache::tags($oid)->get('access_token');
            $accessToken = isset($token) ? decrypt($token) : null;

            if (!empty($accessToken) && $accessToken['expire'] >= $now) return encrypt($accessToken['access_token']);
        }

        if (isset($accessToken) && ($accessToken['expire'] <= $now)) $data = [
            'tenant' => $tenant,
            'client_id' => $clientId,
            'client_secret' => $secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $accessToken['refresh_token'],
        ]; elseif (Arr::has($params, ['code', 'code_challenge'])) $data = [
            'client_id' => $clientId,
            'client_secret' => $secret,
            'code' => $params['code'],
            'redirect_uri' => config('app.url') . '/callback',
            'grant_type' => 'authorization_code',
            'code_verifier' => $params['code_challenge'],
        ];
        else {
            return redirect(route('login'));
        }


        $url = Str::finish(data_get($this->config[$this->provider], 'auth_endpoint'), '/');
        $url .= $tenant;
        $url .= data_get($this->config[$this->provider], 'token_url');

        $response = Http::asForm()->post($url, $data);

        if ($response->successful()) return $this->cacheAccessToken($response->json());

        return redirect(route('login'))->withErrors([
            'error_description' => $response->json('error_description'),
        ]);
    }

    protected function cacheAccessToken($response): string|RedirectResponse
    {
        $expire = time() + $response['expires_in'];
        $token = Arr::only($response, ['access_token', 'refresh_token']);

        $cached = cache()->tags(self::jwt($response['access_token'])['oid'])
            ->put('access_token', encrypt(Arr::add($token, 'expire', $expire)), $expire);

        return $cached
            ? encrypt($response['access_token'])
            : redirect(route('login'))
                ->withErrors(['error_description' => 'Failed to store Access-Token']);
    }

    public static function jwt($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))), true);
    }

    protected function loadConfig()
    {
        $config = $this->providers();
        $config['encrypt'] = true;
        return $config;
    }

    protected function providers()
    {
        return TokenCacheProvider::all([
            'name', 'auth_url', 'token_url', 'auth_endpoint', 'client'
        ])
            ->keyBy('name')
            ->map(fn($item) => collect([
                'client' => json_decode($item->client, true),
                'auth_url' => $item->auth_url,
                'token_url' => $item->token_url,
                'auth_endpoint' => $item->auth_endpoint,
            ]))->toArray();
    }

    /**
     * @throws Exception
     */
    protected function makeRequest()
    {
        $url = $this->getTokenUrl();
        $this->setRequestBody();
        $body = $this->client;
        $body['client_secret'] = decrypt($this->client['client_secret']);
        return Http::asForm()->retry(20, 200)->post($url, $body)->json();

    }

    protected function getKey()
    {
        return $this->config[$this->provider]['client']['client_id'];
    }

    protected function getBaseUrl(): string
    {
        return implode('/', [
            rtrim($this->config[$this->provider]['auth_endpoint'], '/'),
            trim($this->config[$this->provider]['client']['tenant'], '/')
        ]);
    }

    protected function getTokenUrl(): string
    {
        return implode('/', [
            $this->getBaseUrl(),
            trim($this->config[$this->provider]['token_url'], '/')
        ]);
    }

    protected function setRequestBody(): void
    {
        $body = ['grant_type' => 'client_credentials'];
        $providerBody = $this->config[$this->provider]['client'];
        $this->client = array_merge($body, $providerBody);
    }

    public function noCache(): static
    {
        $this->cache = false;
        return $this;
    }

    protected function getToken(): static
    {
        $key = $this->getKey();

        $cached = cache()->tags([$this->provider])->get($key);

        $token = $cached && $this->cache
            ? $cached
            : $this->setToken($key);
        $this->token = $this->config['encrypt'] ? $token : decrypt($token);
        return $this;
    }

    protected function setToken($key): string
    {
        $keys = $this->makeRequest();
        $token = encrypt($keys['access_token']);
        cache()->tags([$this->provider])->add($key, $token, $keys['expires_in']);

        return $token;
    }

    public function __toString(): string
    {
        return $this->token;
    }
}

