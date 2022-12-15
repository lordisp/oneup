<?php

namespace Tests\Feature\Services\Webhook;

use App\Jobs\Scim\ImportUserJob;
use App\Jobs\Scim\UpdateUserJob;
use App\Jobs\Webhook\AlertsChangeStateJob;
use App\Jobs\Webhook\ScimAddMemberJob;
use App\Jobs\Webhook\ScimRemoveMemberJob;
use App\Models\User;
use Database\Seeders\TokenCacheProviderSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Helper;
use Tests\TestCase;

class RemoveMemberWebhookTest extends TestCase
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function remove_members_job_can_be_dispatched()
    {
        Queue::fake([ScimRemoveMemberJob::class]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__ . '/stubs/remove_member_webhook.json'), true)
        )->assertStatus(201);
        Queue::assertPushedOn('webhook', ScimRemoveMemberJob::class);
        Queue::assertPushed(ScimRemoveMemberJob::class, 1);
        Queue::assertNotPushed(ScimAddMemberJob::class);
    }

    /** @test */
    public function remove_members_job_dispatches_user_update_and_alert_change_state_jobs()
    {
        $this->makeAlaProvider();
        Http::fake([
            'https://api.loganalytics.io/v1/*' => Http::response(json_decode(file_get_contents(__DIR__ . '/stubs/remove_member_results.json'), true)),
            'https://login.microsoftonline.com/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/log_analytics_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/arm_token_response.json'), true)),
            'https://graph.microsoft.com/v1.0/users/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member1_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member2_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member3_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member_error_response.json'), true), 404),
        ]);
        Queue::fake([UpdateUserJob::class, AlertsChangeStateJob::class]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__ . '/stubs/remove_member_webhook.json'), true)
        )->assertStatus(201);
        Queue::assertPushed(UpdateUserJob::class, 3);
        Queue::assertPushedOn('webhook', UpdateUserJob::class);
        Queue::assertNotPushed(ImportUserJob::class);
        Queue::assertPushed(AlertsChangeStateJob::class, 2);
        Queue::assertPushedOn('webhook', AlertsChangeStateJob::class);
    }

    /** @test */
    public function remove_members_job_disable_users_in_database()
    {
        $this->makeAlaProvider();
        $this->seed(UserAzureSeeder::class);
        $this->assertDatabaseCount(User::class, 4);
        Http::fake([
            'https://api.loganalytics.io/v1/*' => Http::response(json_decode(file_get_contents(__DIR__ . '/stubs/remove_member_results.json'), true)),
            'https://login.microsoftonline.com/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/log_analytics_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/graph_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/arm_token_response.json'), true)),
            'https://graph.microsoft.com/v1.0/users/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member1_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member2_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member3_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stubs/member_error_response.json'), true), 404),
            'https://management.azure.com/*' => Http::response(json_decode(file_get_contents(__DIR__ . '/stubs/add_member_change_state_close.json'), true)),
        ]);
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__ . '/stubs/remove_member_webhook.json'), true)
        )->assertStatus(201);
        $this->assertCount(3, User::where('status', false)->get());
        $this->assertDatabaseCount(User::class, 4);
    }
}