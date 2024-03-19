<?php

namespace Tests\Feature\Services\Scim;

use App\Jobs\Scim\ImportUserJob;
use App\Models\BusinessService;
use App\Models\User;
use App\Services\Scim;
use Database\Seeders\TokenCacheProviderSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\Helper;
use Tests\TestCase;

class ImportUsesTest extends TestCase
{
    use Helper, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
        $this->scim = new Scim();
    }

    /** @test
     * @throws Exception
     */
    public function throw_validation_exception_by_invalid_provider_name()
    {
        $this->expectExceptionMessage(__('validation.required', ['attribute' => 'provider']));
        $this->scim->provider('');
    }

    /** @test */
    public function throw_exception_if_provider_is_invalid()
    {
        $this->expectExceptionMessage(__('validation.required', ['attribute' => 'provider']));
        $this->scim->provider('invalid');
    }

    /** @test */
    public function can_select_a_provider()
    {
        $instance = $this->scim->provider('lhtest_arm');
        $provider = $this->accessProtected($instance, 'provider');
        $this->assertIsString($provider);
    }

    /** @test */
    public function exception_401_handling_with_waring_log()
    {
        $this->assertDatabaseCount(User::class, 0);
        Http::fake(['graph.microsoft.com/*' => Http::response(status: 401)]);
        Log::shouldReceive('warning')->times(10)->andReturnSelf();
        $this->scim->provider('lhg_graph')
            ->groups('64a289f8-7430-40b4-830f-f64ffd6452fc');
        $this->assertDatabaseCount(User::class, 0);
    }

    /** @test */
    public function exception_404_handling_with_waring_log()
    {
        $this->assertDatabaseCount(User::class, 0);
        Http::fake(['graph.microsoft.com/*' => Http::response(status: 404)]);
        Log::shouldReceive('warning')->times(1)->andReturnSelf();
        $this->scim->provider('lhg_graph')
            ->groups([
                '64a289f8-7430-40b4-830f-f64ffd6452fc', // OneUp Teams
            ]);
        $this->assertDatabaseCount(User::class, 0);
    }

    /** @test */
    public function exception_50x_handling_with_error_log()
    {
        $this->assertDatabaseCount(User::class, 0);
        Http::fake(['graph.microsoft.com/*' => Http::response(status: 500)]);
        Log::shouldReceive('error')->times(10)->andReturnSelf();
        $this->scim->provider('lhg_graph')
            ->groups([
                '64a289f8-7430-40b4-830f-f64ffd6452fc', // OneUp Teams
            ]);
        $this->assertDatabaseCount(User::class, 0);
    }

    /** @test */
    public function successful_response_from_api_and_queue_import()
    {
        $groupMembers = file_get_contents(__DIR__.'/stubs/groupmembers.json');
        Http::fake([
            'graph.microsoft.com/*' => Http::response($groupMembers),
        ]);
        Queue::fake();
        $this->scim->provider('lhg_graph')
            ->groups(['64a289f8-7430-40b4-830f-f64ffd6452fc']);
        Queue::assertPushed(ImportUserJob::class);
    }

    /** @test */
    public function import_users_job_test()
    {
        $this->assertDatabaseCount(User::class, 0);
        $groupMembers = file_get_contents(__DIR__.'/stubs/groupmembers.json');
        Http::fake([
            'graph.microsoft.com/*' => Http::response($groupMembers),
        ]);
        Log::shouldReceive('debug')->once()->andReturnSelf();
        $this->scim->provider('lhg_graph')
            ->groups(['64a289f8-7430-40b4-830f-f64ffd6452fc']);
        $this->assertDatabaseCount(User::class, 1);
    }

    /** @test */
    public function update_users_job_test()
    {
        $groupMembers = file_get_contents(__DIR__.'/stubs/groupmembers.json');
        $user = json_decode($groupMembers, true)['value'][0];
        User::factory()->state([
            'provider' => 'oneup',
            'provider_id' => '1f4db4e4-93c9-4f58-b060-6757b2e621a3',
            'displayName' => $user['displayName'],
            'firstName' => $user['givenName'],
            'lastName' => $user['surname'],
            'email' => $user['mail'],
        ])->create();

        $this->assertDatabaseCount(User::class, 1);
        Http::fake([
            'graph.microsoft.com/*' => Http::response($groupMembers),
        ]);
        Log::shouldReceive('debug')->once()->andReturnSelf();
        $this->scim->provider('lhg_graph')
            ->groups(['64a289f8-7430-40b4-830f-f64ffd6452fc']);
        $this->assertDatabaseCount(User::class, 1);
    }

    /** @test */
    public function can_import_a_user_with_a_business_service()
    {
        (new Scim())
            ->provider('lhg_graph')
            ->users('rafael.camison@austrian.com')
            ->withBusinessService('My_BusinessService_P')
            ->add();
        (new Scim())
            ->provider('lhg_graph')
            ->users('rafael.camison@austrian.com')
            ->withBusinessService('My_BusinessService_P')
            ->add();

        $this->assertDatabaseCount(User::class, 1);
        $this->assertDatabaseCount(BusinessService::class, 1);

        $businessServices = User::whereEmail('rafael.camison@austrian.com')
            ->first()
            ->businessServices
            ->first();

        $this->assertEquals('My_BusinessService_P', $businessServices->name);
    }
}
