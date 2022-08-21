<?php

namespace App\Services;

use App\Models\TokenCacheProvider;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenCache
{
    protected string $token, $provider;

    protected array $config = [], $client = [];

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

    public static function jwt($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
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
        $response = Http::asForm()->retry(10, 200, null, false)->post($url, $body);
        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error(__('tokencache.token_acquire_failed'), $response->json());
            throw new Exception(__('tokencache.token_acquire_failed'),500);
        }
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

    protected function getToken(): static
    {
        $key = $this->getKey();
        $token = cache()->get($key) ?: $this->setToken($key);
        $this->token = $this->config['encrypt'] ? $token : decrypt($token);
        return $this;
    }

    protected function setToken($key): string
    {
        $keys = $this->makeRequest();
        $token = encrypt($keys['access_token']);
        cache()->put($key, $token, $keys['expires_in']);
        return $token;
    }

    public function __toString(): string
    {
        return $this->token;
    }
}

