<?php

namespace Tests\Feature\Services\AzureAD;

use App\Jobs\DismissRiskyUsersJob;
use App\Services\AzureAD\RiskyUserProperties;
use App\Services\AzureAD\RiskyUserTop;
use App\Services\AzureAD\UserRiskState;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Bus\Batch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
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

        $values = data_get($UserRiskState, 'value.*.id');

        $this->assertIsArray($values);

        foreach ($values as $value) {
            $this->assertTrue(Str::isUuid($value));
        }
    }

    /** @test */
    public function can_dispatch_dismiss_risky_users_jobs()
    {
        Bus::fake([DismissRiskyUsersJob::class]);

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

        (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top(new RiskyUserTop(500))
            ->dismiss();

        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->name == 'dismiss-risky-users' &&
                $batch->jobs->count() === 1;
        });
    }

    /** @test */
    public function can_dismiss_risky_users_jobs()
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

        $batch = (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top(new RiskyUserTop(500))
            ->dismiss();

        $this->assertTrue($batch->pendingJobs === 0);

    }

    /** @test */
    public function responds_successfully_if_no_user_required_to_be_dismissed()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => []
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 204)
        ]);

        $batch = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertTrue($batch === null);
    }

    /** @test */
    public function failed_to_dismiss_risky_users()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/riskyUsers*' => Http::sequence()
                ->push([
                    'value' => [
                        [
                            'id' => Str::uuid()->toString(),
                            'isDeleted' => false,
                        ]
                    ]
                ], 204)
                ->push(status: 500)
        ]);

        $batch = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertTrue($batch->failedJobs === 1);
    }

    /** @test */
    public function api_will_retry_on_error()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::sequence()
                ->push(status: 401)
                ->push(status: 401)
                ->push(status: 401)
                ->push(status: 401)
                ->push([
                    'value' => [
                        [
                            'id' => Str::uuid()->toString(),
                            'isDeleted' => false,
                        ]
                    ]
                ], 204)
                ->push(status: 204)

        ]);

        $dismissedUsers = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->isInstanceOf(Batch::class);
        $this->assertFalse($dismissedUsers->hasFailures());
    }
}
