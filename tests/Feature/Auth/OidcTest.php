<?php

namespace Tests\Feature\Auth;

use App\Facades\TokenCache;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\TokenCacheProviderSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OidcTest extends TestCase
{
    use RefreshDatabase;

    public const provider = 'lhg_graph';

    public const code = '0.AS8AFFXhculbqEaLC6-bG3ezuO5JLsY8i7xFnYBz8_ctTEEvAHA.AgABAAIAAAD--DLA3VO7QrddgJg7WevrAgDs_wQA9P9CDexkQAUqP572-sepSs9_qSuV-ZkXFSJH8UwuvIvclbuBt5AqBuB6ucr-G9z1RudVEfd_PRQ5dhkJfrFk32S5xeNimxpBZ6XgaRYlFC9fMrKXZTTYeC2QmzAoERVvTvByNlskhOEkHhGDu039CuTlcFLIHjPPKq0tWVIk5YHjBfJr2nlufgKaGvSH6jDrAVvpTy2JE1buHR3_qERInaK6DcaTHuRCKZRJmecE2BXxZKh_bnQbzYTmE0Fojy8V_KJwvyol2nE7PrL0r7CZ19pPq-2AHJA0RpP6auQwY8ki1hulleRbqvmO14B28IRO0OBHwJ-Ym1lj0Vh_OnJbyMd6tPrOAIOcevxwA5y-4Fi3KMvQM1a1jLvCYpj58YRKu8ktEjvK1OAJe8e8tkh2PrqbdD8zJbp9LZ3F-DzYE6CVdy6NV6vYHumr8uES-3L05c--KxYTJbdCg0YkAQOVpbtrXs2X4s8Q8O71ki6ufQfZkn9XQHsfVxtVr-Gtx3Zf5hIO7ixnBm6f9O-8o4yEbwSXaL-B-iuNxMLpetqqszgqlO7bWDL7ISxfVCC3cbIiVzD_7X3dOOzvBj3uJnQImCHhwppMjfKMHrfyEDPEQi9UYxh6Yho8oYY4m3KMQj4KurXngsuxtPnpqZEtXi76KKoMOn7iaiZmkFZqOvLKOZLf2aFsomOJSAxWHt3A9RwM6WHAsoBETE5UhlO055jgFHPbnq-fSOXckqCawmtv5Hv5vnh_OmjS4zrbvXqC4qB8Z9S2plH3D2xq898Lq5dNNIXa_n5rKrmubiXAw6UmBFCXEpjFmh85tCxFfFzrOjDRAJAzAwQ5K4uDEkhokWFn_YoRdvdt_iLzP2cDkqvIuz0nTrUpsB_hXtwSL9zsu9LXJM5cYLxaLA&state=498d9e97a9117d0aad6a2f5d1aba87a316c2f6d2d80f92aa920f64f31fa4b674&session_state=52dd6258-5e1b-450d-a492-62e98158407d';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TokenCacheProviderSeeder::class, UserAzureSeeder::class]);
    }

    /** @test */
    public function redirect_to_azure_idp_and_set_auth_state_to_session(): void
    {
        $this->post('/signin')
            ->assertRedirect()
            ->assertSessionHas('authState');
    }

    /** @test */
    public function can_acquire_an_code_access_token(): void
    {
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(
            json_decode(file_get_contents(base_path('/tests/Feature/Stubs/oidc_access_token.json')), true)
        )]);

        TokenCache::provider(self::provider)->authCode();

        $state = session()->get('authState');

        $token = TokenCache::provider(self::provider)
            ->accessToken(['code' => $state, 'code_challenge' => $state]);

        $jwt = TokenCache::jwt(decrypt($token));

        $this->assertArrayHasKey('aud', $jwt);
        $this->assertArrayHasKey('iss', $jwt);
        $this->assertArrayHasKey('iat', $jwt);
        $this->assertArrayHasKey('oid', $jwt);
        $this->assertArrayHasKey('tid', $jwt);
    }

    /** @test */
    public function can_login_over_oidc(): void
    {
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(
            json_decode(file_get_contents(base_path('/tests/Feature/Stubs/oidc_access_token.json')), true)
        )]);

        $this->assertGuest();
        session(['authState' => '498d9e97a9117d0aad6a2f5d1aba87a316c2f6d2d80f92aa920f64f31fa4b674']);
        $this->get('/callback?code='.self::code)
            ->assertRedirect(AppServiceProvider::HOME);

        $this->assertAuthenticated();
    }

    /** @test
     *@depends  can_login_over_oidc
     */
    public function user_can_logout_from_oidc(): void
    {
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(
            json_decode(file_get_contents(base_path('/tests/Feature/Stubs/oidc_access_token.json')), true)
        )]);

        session(['authState' => '498d9e97a9117d0aad6a2f5d1aba87a316c2f6d2d80f92aa920f64f31fa4b674']);

        $this->get('/callback?code='.self::code);

        $this->assertAuthenticated();

        $user = User::first();

        $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();

    }
}
