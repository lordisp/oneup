<?php

namespace Tests\Feature\Services\AzureAD;

use App\Exceptions\MsGraphException;
use App\Services\AzureAD\MsGraph;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MsGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([TokenCacheProviderSeeder::class]);
    }

    /** @test
     * @throws MsGraphException
     */
    public function the_users_endpoint_returns_max_100_values_with_expected_fields()
    {
        $results = MsGraph::get('/users')
            ->call();

        $this->assertIsArray($results);
        $this->assertCount(100, data_get($results, 'value'));

        $first = data_get($results, 'value')[0];
        $this->assertArrayHasKey('businessPhones', $first);
        $this->assertArrayHasKey('displayName', $first);
        $this->assertArrayHasKey('givenName', $first);
        $this->assertArrayHasKey('jobTitle', $first);
        $this->assertArrayHasKey('mail', $first);
        $this->assertArrayHasKey('mobilePhone', $first);
        $this->assertArrayHasKey('officeLocation', $first);
        $this->assertArrayHasKey('preferredLanguage', $first);
        $this->assertArrayHasKey('surname', $first);
        $this->assertArrayHasKey('userPrincipalName', $first);
        $this->assertArrayHasKey('id', $first);
    }

    /** @test
     * @throws MsGraphException
     */
    public function the_users_beta_endpoint_returns_max_100()
    {
        $results = MsGraph::get('/users')
            ->beta()
            ->call();

        $this->assertIsArray($results);
        $this->assertCount(100, data_get($results, 'value'));
    }

    /** @test
     * @throws MsGraphException
     */
    public function it_uses_specified_provider_to_fetch_data_from_another_tenant()
    {
        cache()->flush();
        $results = MsGraph::get('/users')
            ->provider('lhtest_graph')
            ->select('userPrincipalName')
            ->filter('endsWith(userPrincipalName,\'@lhtests.onmicrosoft.com\')')
            ->top(1)
            ->call();

        $this->assertIsArray($results);
        $this->assertCount(1, data_get($results, 'value'));

        $first = data_get($results, 'value')[0];
        $this->assertArrayHasKey('userPrincipalName', $first);
        $this->assertStringEndsWith('@lhtests.onmicrosoft.com', mb_strtolower(data_get($first, 'userPrincipalName')));
    }

    /** @test
     * @throws MsGraphException
     */
    public function fetching_all_users_with_take_attribute_returns_expected_fields()
    {
        $results = MsGraph::get('/users')
            ->all(1001)
            ->select('id,displayName,givenName,surname,mail,userPrincipalName')
            ->call();

        $this->assertArrayHasKey('value', $results);
        $this->assertCount(1001, data_get($results, 'value'));

        $first = data_get($results, 'value')[0];
        $this->assertArrayHasKey('displayName', $first);
        $this->assertArrayHasKey('givenName', $first);
        $this->assertArrayHasKey('surname', $first);
        $this->assertArrayHasKey('mail', $first);
        $this->assertArrayHasKey('userPrincipalName', $first);
        $this->assertArrayHasKey('id', $first);
    }

    /** @test
     * @throws MsGraphException
     */
    public function an_invalid_top_parameter_throws_error_exception()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__('validation.max.numeric', ['attribute' => 'top', 'max' => 999]));

        MsGraph::get('/users')
            ->top(1000)
            ->call();
    }

    /** @test
     * @throws MsGraphException
     */
    public function it_returns_only_the_amount_of_top_n_results_of_the_first_iteration()
    {
        $results = MsGraph::get('/users')
            ->top(3)
            ->call();

        $this->assertCount(3, data_get($results, 'value'));
    }

    /** @test */
    public function it_applies_multiple_filters_to_users_query()
    {
        $results = MsGraph::get('/Users')
            ->filter("startswith(surname,'Camison')")
            ->filter("startswith(givenName,'Rafael')")
            ->filter("endsWith(mail,'austrian.com')")
            ->call();

        $this->assertArrayHasKey('value', $results);
    }

    /** @test
     * @throws MsGraphException
     */
    public function it_retrieves_users_based_on_displayName_search()
    {
        $results = MsGraph::get('/Users')
            ->search('displayName', 'Camison')
            ->call();

        $this->assertArrayHasKey('value', $results, 'The $results does not have the value key');
        $this->assertArrayHasKey('@odata.count', $results, 'The $results does not have the @odata.count key');
        $this->assertArrayHasKey('@odata.context', $results, 'The $results does not have the @odata.context key');

        $values = data_get($results, 'value.*.surname');
        $values = array_map('mb_strtolower', $values);
        $values = array_unique($values);

        $this->assertCount(1, $values);
        $this->assertEquals('camison', $values[0], 'The search is not match the surname');
    }

    /** @test
     * @throws MsGraphException
     */
    public function test_the_next_link_method()
    {
        Http::fake([
            'https://graph.microsoft.com/*' => Http::sequence()
                ->push(status: 429, headers: ['Retry-After' => 0])
                ->push(['value' => []], 200),
        ]);

        $results = MsGraph::get('/users')
            ->call();

        $this->assertIsArray($results);
    }

    /** @test
     * @throws MsGraphException
     */
    public function can_revoke_sessions_from_an_given_user_by_id()
    {
        cache()->flush();

        $results = MsGraph::post('/users/67ca2374-1e95-4b38-b2b6-45bf89ced946/revokeSignInSessions')
            ->call();

        $this->assertIsArray($results);
        $this->assertTrue(data_get($results, 'value'));
    }

    /** @test
     * @throws MsGraphException
     */
    public function can_revoke_sessions_from_an_given_user_by_principal_name()
    {
        cache()->flush();

        $results = MsGraph::post('/users/A300250@dlh.de/revokeSignInSessions')
            ->call();

        $this->assertIsArray($results);
        $this->assertTrue(data_get($results, 'value'));
    }

    /** @test */
    public function request_is_not_retried_when_404_received()
    {
        Http::fake([
            'https://graph.microsoft.com/*' => Http::response([], 404),
        ]);

        $response = MsGraph::get('/users/67ca2374-1e95-4b38-b2b6-45bf89ced946')
            ->call();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    /** @test */
    public function it_throws_MsGraphException_on_403_request_exception()
    {
        cache()->flush();
        Http::fake([
            'https://graph.microsoft.com/*' => Http::response(['error' => 'message'], 403),
        ]);

        $this->expectException(MsGraphException::class);
        $this->expectExceptionMessage('message');

        $result = MsGraph::get('/users/1710802e-dc0e-4794-b9b8-a24349c27627')
            ->call();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_throws_MsGraphException_on_400_request_exception()
    {
        cache()->flush();
        Http::fake([
            'https://graph.microsoft.com/*' => Http::response(['error' => 'message'], 400),
        ]);

        $this->expectException(MsGraphException::class);
        $this->expectExceptionMessage('message');

        $result = MsGraph::get('/users/1710802e-dc0e-4794-b9b8-a24349c27627')
            ->call();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function test_expired_token_is_refreshed()
    {
        cache()->flush();
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::sequence()
                ->push(['error' => 'message'], 401)
                ->push(['value' => []], 200),
        ]);

        $result = MsGraph::get('/users/1710802e-dc0e-4794-b9b8-a24349c27627')
            ->call();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
    }

    /** @test */
    public function it_throws_MsGraphException_on_500_request_exception()
    {
        cache()->flush();
        Http::fake([
            'https://graph.microsoft.com/*' => Http::response(['error' => 'message'], 500),
        ]);

        $this->expectException(MsGraphException::class);
        $this->expectExceptionMessage('message');

        $result = MsGraph::get('/users/1710802e-dc0e-4794-b9b8-a24349c27627')
            ->call();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function test_request_timeout_exception_is_retried()
    {
        cache()->flush();
        Http::fake([
            'https://graph.microsoft.com/*' => Http::sequence()
                ->push(status: 408)
                ->push(['value' => []], 200),
        ]);

        $result = MsGraph::get('/users/1710802e-dc0e-4794-b9b8-a24349c27627')
            ->call();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
    }

    /** @test
     * @throws MsGraphException
     */
    public function it_retrieves_the_specified_number_of_users_across_multiple_links()
    {
        cache()->flush();
        $result = MsGraph::get('/users')
            ->all(201)
            ->call();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertCount(201, data_get($result, 'value'));
    }

    /** @test
     * @throws MsGraphException
     */
    public function it_returns_the_body_of_the_response_without_error()
    {
        cache()->flush();
        $result = MsGraph::get('/users/rafael.camison@austrian.com/photos/120x120/$value')
            ->body();
        $this->assertIsString($result);
        $this->assertStringNotContainsString('ImageNotFoundException', $result);
    }

    /** @test
     * @throws MsGraphException
     */
    public function it_returns_the_body_of_the_response_with_error()
    {
        cache()->flush();
        $result = MsGraph::get('/users/stephan.abel@dlh.de/photos/120x120/$value')
            ->body();

        $this->assertIsString($result);
        $this->assertStringContainsString('ImageNotFoundException', $result);
    }
}
