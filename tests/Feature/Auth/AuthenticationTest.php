<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
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
    public function users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
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
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect('/');
    }

}
