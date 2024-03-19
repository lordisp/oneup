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
    public function invalid_property_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RiskyUserProperties('foo');
    }

    /** @test */
    public function valid_property_returns_its_name(): void
    {
        $validProperties = [
            '@odata.type',
            'id',
            'isDeleted',
            'isProcessing',
            'riskLastUpdatedDateTime',
            'riskLevel',
            'riskState',
            'riskDetail',
            'userDisplayName',
            'userPrincipalName',
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
    public function valid_top_returns_its_value(): void
    {
        for ($i = 1; $i <= 500; $i++) {
            $this->assertTrue($i == (new RiskyUserTop($i))->get());
        }
    }

    /** @test */
    public function invalid_top_throws_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RiskyUserTop(501))->get();
    }

    protected function fakeListRiskyUsers(int $count = 500): \GuzzleHttp\Promise\PromiseInterface
    {
        for ($i = 1; $i <= $count; $i++) {
            $value[] = [
                'id' => Str::uuid()->toString(),
                'isDeleted' => false,
                'riskState' => 'atRisk',
            ];
        }
        $response = [
            '@odata.context' => 'https://graph.microsoft.com/v1.0/$metadata#identityProtection/riskyUsers(id,riskState,isDeleted)',
            '@odata.nextLink' => 'https://graph.microsoft.com/v1.0/identityProtection/riskyUsers?$select=id%2criskState%2cisDeleted&$top=500&$filter=riskState+eq+%27atRisk%27+and+isDeleted+eq+false+and+isProcessing+eq+false&$skiptoken=389e075f59896e6ef4b385f94e130473edc479c91ecddacbb707404f0343a259_500',
            'value' => $value ?? [],
        ];

        return Http::response($response);
    }

    /** @test */
    public function can_list_all_risky_users_objectId(): void
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => $this->fakeListRiskyUsers(),
        ]);

        $UserRiskState = (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->list();

        $values = data_get($UserRiskState, 'value');

        $this->assertIsArray($values);
        $this->assertCount(500, $values);

        foreach ($values as $value) {
            $this->assertTrue(Str::isUuid(data_get($value, 'id')));
            $this->assertFalse(data_get($value, 'isDeleted'));
            $this->assertEquals('atRisk', data_get($value, 'riskState'));
        }
    }

    /** @test */
    public function risky_users_returns_request_exception(): void
    {
        Log::shouldReceive('error')->withArgs(function ($message, $context) {
            return $message == 'RiskyUsers API Error' &&
                $context['service'] == 'risky-users' &&
                $context['message'] == 'Bad Request' &&
                $context['status'] == 400;
        })->once();

        Log::shouldReceive('info')->withArgs(function ($message, $context) {
            return $message == 'No RiskyUsers to dismiss' && $context['service'] == 'risky-users';
        })->once();

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response(status: 400),
        ]);

        (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertTrue(true);
    }

    /** @test */
    public function can_dispatch_dismiss_risky_users_jobs(): void
    {
        Bus::fake([DismissRiskyUsersJob::class]);

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'isDeleted' => false,
                    ],
                ],
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 204),
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
    public function can_dismiss_risky_users_jobs(): void
    {
        Log::shouldReceive('info')->once();
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'isDeleted' => false,
                    ],
                ],
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 204),
        ]);

        (new UserRiskState)
            ->select(new RiskyUserProperties(['id', 'riskState', 'isDeleted']))
            ->atRisk()
            ->top(new RiskyUserTop(500))
            ->dismiss();

        $this->assertTrue(true);

    }

    /** @test */
    public function responds_successfully_if_no_user_required_to_be_dismissed(): void
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/*' => Http::response([
                'value' => [],
            ], 204),
            'https://graph.microsoft.com/beta/riskyUsers/dismiss' => Http::response(status: 204),
        ]);

        $batch = (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->assertTrue($batch === null);
    }

    /** @test */
    public function failed_to_dismiss_risky_users(): void
    {
        Log::shouldReceive('info')->never();
        Log::shouldReceive('error')->once();

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/v1.0/identityProtection/riskyUsers*' => Http::sequence()
                ->push([
                    'value' => [
                        [
                            'id' => Str::uuid()->toString(),
                            'isDeleted' => false,
                        ],
                    ],
                ], 204)
                ->push(status: 400)
                ->push(status: 400)
                ->push(status: 400)
                ->push(status: 400)
                ->push(status: 400),
        ]);

        /* Act */
        (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        /* Assert */
        $this->assertTrue(true);
    }

    /** @test */
    public function api_will_retry_on_error(): void
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
                        ],
                    ],
                ], 204)
                ->push(status: 204),
        ]);

        (new UserRiskState)
            ->select(new RiskyUserProperties('id'))
            ->atRisk()
            ->top((new RiskyUserTop(500)))
            ->dismiss();

        $this->isInstanceOf(Batch::class);
        $this->assertFalse(false);
    }
}
