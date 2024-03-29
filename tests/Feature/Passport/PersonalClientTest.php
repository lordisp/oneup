<?php

namespace Tests\Feature\Passport;

use App\Models\Passport\Client;
use App\Models\Passport\PersonalAccessClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenResult;
use Tests\Helper;
use Tests\TestCase;

class PersonalClientTest extends TestCase
{
    use Helper, RefreshDatabase;

    /** @test */
    public function can_create_a_personal_access_client(): void
    {
        Passport::$hashesClientSecrets = false;

        $this->artisan(
            'passport:client',
            ['--name' => config('app.name'), '--personal' => null]
        )->assertSuccessful();

        $this->assertDatabaseCount(PersonalAccessClient::class, 1);
    }

    /** @test
     * @depends can_create_a_personal_access_client
     */
    public function can_issue_a_personal_access_token(): void
    {
        $this->createPersonalClient();

        $user = User::factory()->create()->createToken('test');

        $this->assertInstanceOf(PersonalAccessTokenResult::class, $user);

        $this->assertArrayHasKey('accessToken', get_object_vars($user));

        $this->assertArrayHasKey('token', get_object_vars($user));
    }

    /** @test
     * @depends can_create_a_personal_access_client
     */
    public function can_issue_a_personal_access_toke_with_scope(): void
    {
        $this->createPersonalClient();

        Passport::tokensCan([
            'do-something' => 'Do something amazing stuff!',
        ]);

        $user = User::factory()->create()->createToken('test', ['do-something']);

        $this->assertArrayHasKey('accessToken', get_object_vars($user));

        $this->assertArrayHasKey('token', get_object_vars($user));

        $this->assertEquals(['do-something'], $user->token->scopes);
    }

    /** @test
     * @depends can_create_a_personal_access_client
     */
    public function list_the_scopes_a_user_may_assign_to_a_personal_access_token(): void
    {
        $this->createPersonalClient();

        $user = User::factory()->create();

        $user->createToken('test');

        $response = $this->actingAs($user)->get('/oauth/scopes');

        $this->assertInstanceOf(Collection::class, $response->getOriginalContent());
    }
}
