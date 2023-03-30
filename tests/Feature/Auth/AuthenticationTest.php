<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Database\Seeders\TokenCacheProviderSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function users_will_be_redirected_if_unauthenticated()
    {
        $response = $this->get('dashboard');

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_logout_over_auth_controller()
    {
        $user = User::factory()->create();

        auth()->login($user);
        $this->assertAuthenticatedAs($user);

        (new AuthController())->logout();
        $this->assertGuest();
    }

    /** @test */
    public function user_can_log_out_over_api()
    {
        $this->seed([TokenCacheProviderSeeder::class,UserAzureSeeder::class]);

        $user = User::first();

        $this->actingAs($user)->post(route('logout'))->assertRedirect('/');
    }

}
