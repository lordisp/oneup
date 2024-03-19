<?php

namespace Tests\Feature\Services\Webhook;

use App\Jobs\Scim\ImportUserJob;
use App\Jobs\Webhook\AlertsChangeStateJob;
use App\Jobs\Webhook\ScimAddMemberJob;
use App\Models\User;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Helper;
use Tests\TestCase;

class AddMemberWebhookTest extends TestCase
{
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function add_members_job_can_be_dispatched(): void
    {
        Queue::fake([ScimAddMemberJob::class]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__.'/stubs/add_member_webhook.json'), true)
        )->assertStatus(201);
        Queue::assertPushedOn('webhook', ScimAddMemberJob::class);
        Queue::assertPushed(ScimAddMemberJob::class, 1);
    }

    /** @test */
    public function remove_members_job_dispatches_user_import_and_alert_change_state_jobs(): void
    {
        $this->makeAlaProvider();
        Http::fake([
            'https://api.loganalytics.io/v1/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/add_member_results.json'), true)),
            'https://login.microsoftonline.com/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/log_analytics_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/arm_token_response.json'), true)),
            'https://graph.microsoft.com/v1.0/users/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member1_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member2_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member3_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member_error_response.json'), true), 404),

        ]);
        Queue::fake([ImportUserJob::class, AlertsChangeStateJob::class]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__.'/stubs/add_member_webhook.json'), true)
        )->assertStatus(201);
        Queue::assertPushed(ImportUserJob::class, 3);
        Queue::assertPushedOn('webhook', ImportUserJob::class);
        Queue::assertPushed(AlertsChangeStateJob::class, 2);
        Queue::assertPushedOn('webhook', AlertsChangeStateJob::class);
    }

    /** @test */
    public function add_members_job_retrieves_data_from_log_analytics_api_and_dispatch_import(): void
    {
        $this->makeAlaProvider();
        Http::fake([
            'https://api.loganalytics.io/v1/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/add_member_results.json'), true)),
            'https://login.microsoftonline.com/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/log_analytics_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/graph_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/arm_token_response.json'), true)),
            'https://graph.microsoft.com/v1.0/users/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member1_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member2_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member3_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member_error_response.json'), true), 404),
            'https://management.azure.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/add_member_change_state_close.json'), true)),
        ]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__.'/stubs/add_member_webhook.json'), true)
        )->assertStatus(201);
        $this->assertDatabaseCount(User::class, 3);
    }

    /** @test */
    public function add_members_job_imports_user_to_database(): void
    {
        $this->makeAlaProvider();
        $this->assertDatabaseCount(User::class, 0);
        Http::fake([
            'https://api.loganalytics.io/v1/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/add_member_results.json'), true)),
            'https://login.microsoftonline.com/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/log_analytics_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/graph_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/arm_token_response.json'), true)),
            'https://graph.microsoft.com/v1.0/users/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member1_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member2_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member3_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/member_error_response.json'), true), 404),
            'https://management.azure.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/add_member_change_state_close.json'), true)),
        ]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__.'/stubs/add_member_webhook.json'), true)
        )->assertStatus(201);
        $this->assertDatabaseCount(User::class, 3);
    }
}
