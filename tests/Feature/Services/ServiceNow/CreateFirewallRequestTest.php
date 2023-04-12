<?php

namespace Tests\Feature\Services\ServiceNow;

use App\Jobs\ServiceNow\ImportBusinessServiceMemberJob;
use App\Jobs\ServiceNow\ImportFirewallRequestJob;
use App\Models\FirewallRule;
use App\Models\User;
use App\Notifications\CreateFirewallRequestNotification;
use App\Services\ServiceNow\CreateFirewallRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateFirewallRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importRules();
    }

    /** @test */
    public function a_firewall_request_returns_a_successful_response()
    {
        // Arrange
        $user = User::factory()->create();
        $id = FirewallRule::first()->id;

        Notification::fake();
        Http::fake(['https://lhgroup.service-now.com/*' => Http::response([
            'result' => [
                'status' => 'Success',
                'requestNumber' => 'REQ000123456',
                'requestItmNumber' => 'RITM000123456',
            ]
        ])]);

        // Act
        $response = CreateFirewallRequest::process($id, $user);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
        $this->assertEquals('Success', $response->json('result')['status']);
        $this->assertEquals('REQ000123456', $response->json('result')['requestNumber']);
        $this->assertEquals('RITM000123456', $response->json('result')['requestItmNumber']);
    }

    /** @test */
    public function a_connection_timeout_force_a_call_retry()
    {
        // Arrange
        $user = User::factory()->create();
        $id = FirewallRule::first()->id;

        Notification::fake();
        Http::fake(['https://lhgroup.service-now.com/*' => Http::sequence()
            ->pushStatus(408)
            ->push([
                'result' => [
                    'status' => 'Success',
                    'requestNumber' => 'REQ000123456',
                    'requestItmNumber' => 'RITM000123456',
                ]
            ])]);

        // Act
        $response = CreateFirewallRequest::process($id, $user);

        Notification::assertSentTo($user, CreateFirewallRequestNotification::class, 1);

        $this->assertEquals('Success', $response->json('result')['status']);
    }

    /** @test */
    public function bad_requests()
    {
        // Arrange
        $user = User::factory()->create();
        $id = FirewallRule::first()->id;

        Notification::fake();
        Http::fake(['https://lhgroup.service-now.com/*' => Http::sequence()
            ->pushStatus(400)
        ]);

        // Act
        $response = CreateFirewallRequest::process($id, $user);

        // Assert
        Notification::assertSentTo($user, CreateFirewallRequestNotification::class, 1);
        $this->assertEquals(400, $response->status());
    }

    /** @test */
    public function it_sends_a_mail_notification_to_the_user()
    {
        // Arrange
        $user = User::factory()->create();
        $ruleId = '12365489';

        Notification::fake();
        Http::fake(['https://lhgroup.service-now.com/*' => Http::sequence()
            ->push([
                'result' => [
                    'status' => 'Success',
                    'requestNumber' => 'REQ000123456',
                    'requestItmNumber' => 'RITM000123456',
                ]
            ])]);

        // Act
        $response = CreateFirewallRequest::process($ruleId, $user);

        // Assert
        Notification::assertSentTo($user, CreateFirewallRequestNotification::class, 1);
        $this->assertStringContainsString($response->content(), 'Rule with the Id \'12365489\' was not found!');
    }

    protected function importRules()
    {
        Queue::fake(ImportBusinessServiceMemberJob::class);

        $fileContents = json_decode(file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/valid.json'), true);;

        foreach ($fileContents as $fileContent) {
            ImportFirewallRequestJob::dispatch(User::factory()->create(), $fileContent);
        }
    }
}