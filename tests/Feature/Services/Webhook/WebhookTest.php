<?php

namespace Tests\Feature\Services\Webhook;

use App\Jobs\WebhookJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Queue;
use Tests\Helper;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase, Helper;

    /** @test
     * @url https://learn.microsoft.com/en-us/rest/api/monitor/alertsmanagement/alerts/change-state?tabs=HTTP#alertstate
     */
    public function webhook_can_accept_data_to_process_by_middleware()
    {
        $this->makeAlaProvider();
        Queue::fake();
        $this->post('/api/v1/webhook',
            json_decode(file_get_contents(__DIR__ . '/stubs/add_member_webhook.json'), true)
        )->assertStatus(201);
        Queue::assertPushedOn('admin', WebhookJob::class);
    }

    /** @test */
    public function webhook_returns_400_if_body_is_invalid()
    {
        Queue::fake();
        $this->post('api/v1/webhook', ['data' => ['foo']])->assertStatus(400);
        Queue::assertNothingPushed();
    }

    /** @test */
    public function hit_rate_limit_if_more_than_allowed_requests_where_made()
    {
        $i = 1;
        while ($i < 61) {
            $this->post('api/v1/webhook', ['data' => ['foo']]);
            $i++;
        }
        $this->post('api/v1/webhook', ['data' => ['foo']])
        ->assertStatus(429);
    }
}
