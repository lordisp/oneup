<?php

namespace Tests\Feature\Services\AzureAD;

use App\Facades\AzureAD\User;
use App\Services\AzureAD\UserId;
use App\Services\AzureAD\UserPrincipal;
use App\Services\AzureAD\UserProperties;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function get_an_user_from_azure_ad_by_user_principal_name(): void
    {
        $userPrincipal = 'rafael.camison@austrian.com';

        $user = User::get(new UserPrincipal($userPrincipal));

        $this->assertEquals(Str::lower($user['userPrincipalName']), $userPrincipal);
    }

    /** @test */
    public function get_an_user_from_azure_ad_by_user_id(): void
    {
        // Arrange
        $userId = '1f4db4e4-93c9-4f58-b060-6757b2e621a3';
        $principalId = new UserId($userId);

        // Act
        $user = User::get($principalId);

        //Assert
        $this->assertEquals(Str::lower($user['id']), $userId);
    }

    /** @test */
    public function can_select_specific_user_properties(): void
    {
        // Arrange
        $userPrincipal = 'rafael.camison@austrian.com';
        $principalId = new UserPrincipal($userPrincipal);
        $properties = new UserProperties('id,displayName');

        // Act
        $user = User::select($properties)
            ->get($principalId);

        // Assert
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('displayName', $user);
        $this->assertArrayNotHasKey('businessPhones', $user);
        $this->assertArrayNotHasKey('givenName', $user);
        $this->assertArrayNotHasKey('jobTitle', $user);
        $this->assertArrayNotHasKey('mail', $user);
        $this->assertArrayNotHasKey('mobilePhone', $user);
        $this->assertArrayNotHasKey('officeLocation', $user);
        $this->assertArrayNotHasKey('preferredLanguage', $user);
        $this->assertArrayNotHasKey('surname', $user);
        $this->assertArrayNotHasKey('userPrincipalName', $user);

        $this->assertEquals('CAMISON, RAFAEL', $user['displayName']);
    }

    /** @test */
    public function it_returns_an_error_badge_if_user_was_not_found(): void
    {
        $user = User::get(new UserPrincipal('first.name@domain.com'));

        $this->assertStringContainsString('does not exist', data_get($user, 'error.message'));
        $this->assertEquals('Request_ResourceNotFound', data_get($user, 'error.code'));
    }

    /** @test */
    public function it_tries_again_to_call_the_api_if_an_unknown_error_occurs(): void
    {
        $userPrincipal = 'rafael.camison@austrian.com';

        Http::fake(['https://graph.microsoft.com/v1.0/users/*' => Http::sequence()
            ->pushStatus(401)
            ->push([
                '@odata.context' => 'https://graph.microsoft.com/v1.0/$metadata#users/$entity',
                'businessPhones' => [],
                'displayName' => 'CAMISON, RAFAEL',
                'givenName' => 'Rafael',
                'jobTitle' => 'CAMISON, RAFAEL',
                'mail' => 'rafael.camison@austrian.com',
                'mobilePhone' => '+43 664 123456789',
                'officeLocation' => 'Mauritius',
                'preferredLanguage' => 'en-US',
                'surname' => 'Camison',
                'userPrincipalName' => 'rafael.camison@austrian.com',
                'id' => '48d31887-5fad-4d73-a9f5-3c356e68a038',
            ])]);

        $user = User::get(new UserPrincipal($userPrincipal));

        $this->assertEquals(Str::lower($user['userPrincipalName']), $userPrincipal);
    }
}
