<?php

namespace Tests\Feature\Services\AzureAD;

use App\Services\AzureAD\RiskyUserProperties;
use App\Services\AzureAD\RiskyUserTop;
use App\Services\AzureAD\UserRiskState;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class RiskyUserResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([TokenCacheProviderSeeder::class]);
    }

    /** @test */
    public function invalid_property_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        new RiskyUserProperties('foo');
    }

    /** @test */
    public function valid_property_returns_its_name()
    {
        $validProperties = [
            "@odata.type",
            "id",
            "isDeleted",
            "isProcessing",
            "riskLastUpdatedDateTime",
            "riskLevel",
            "riskState",
            "riskDetail",
            "userDisplayName",
            "userPrincipalName",
        ];

        foreach ($validProperties as $property) {
            $this->assertEquals(
                $property,
                (new RiskyUserProperties($property))
                    ->get()
            );
        }
    }

    /** @test */
    public function valid_top_returns_its_value()
    {
        for ($i = 1; $i <= 500; $i++) {
            $this->assertTrue($i == (new RiskyUserTop($i))->get());
        }
    }

    /** @test */
    public function invalid_top_throws_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        (new RiskyUserTop(501))->get();
    }

    /** @test */
    public function can_list_all_risky_users_objectId()
    {
        $UserRiskState = (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->list();

        $values = array_filter(data_get($UserRiskState, 'value'), fn($item) => data_get($item, 'isDeleted') === false);

        $values = data_get($values, '*.id');

        $this->assertIsArray($values);

        foreach ($values as $value) {
            $this->assertTrue(Str::isUuid($value));
        }
    }

    /** @test */
    public function can_dismiss_risky_users()
    {
        Log::shouldReceive('info')->once();

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'isDeleted' => false,
                    ]
                ]
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 204)
        ]);

        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertEquals(204, $dismissedUsers->status());
    }

    /** @test */
    public function responds_successfully_if_no_user_required_to_be_dismissed()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => [
                    []
                ]
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 204)
        ]);

        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertEquals(200, $dismissedUsers->status());
    }

    /** @test */
    public function failed_to_dismiss_risky_users()
    {
        Log::shouldReceive('info')->once();

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'isDeleted' => false,
                    ]
                ]
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 500)
        ]);

        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertEquals(204, $dismissedUsers->status());
    }

    /** @test */
    public function api_will_retry_on_error()
    {
        $this->expectException(RequestException::class);

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::sequence()
                ->push(status: 404)
                ->push(status: 503)
                ->push(status: 500)
        ]);

        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertTrue(true);
    }
}
