<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TokenCacheProvider>
 */
class TokenCacheProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = [
            'tenant' => Str::uuid(),
            'client_id' => Str::uuid(),
            'client_secret' => encrypt(Str::random(40)),
            'scope' => 'https://graph.microsoft.com/.default',
        ];

        return [
            'name' => Str::lower($this->faker->word()),
            'auth_url' => '/oauth2/v2.0/authorize',
            'token_url' => '/oauth2/v2.0/token',
            'auth_endpoint' => 'https://login.microsoftonline.com',
            'client' => json_encode($client),
        ];
    }
}
