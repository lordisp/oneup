<?php

namespace Tests\Feature\Services\ServiceNow;

use App\Http\Livewire\PCI\FirewallRulesRead;
use App\Jobs\ServiceNow\ImportBusinessServiceMemberJob;
use App\Jobs\ServiceNow\ImportFirewallRequestJob;
use App\Models\BusinessService;
use App\Models\FirewallRule;
use App\Models\Subnet;
use App\Models\User;
use App\Notifications\CreateFirewallRequestNotification;
use App\Services\ServiceNow\CreateFirewallRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
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
        $rule = FirewallRule::with(['businessService'])->first();

        Notification::fake();

        Http::fake([config('servicenow.uri') . '/*' => Http::sequence()
            ->push(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/request.json')), true))
        ]);

        // Act
        $response = CreateFirewallRequest::process($rule, $user);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json());
        $this->assertEquals('Success', $response->json('result')['status']);
        $this->assertEquals('REQ0032701', $response->json('result')['requestNumber']);
        $this->assertEquals('ROT0033162', $response->json('result')['requestItemNumber']);
    }

    /** @test */
    public function a_connection_timeout_force_a_call_retry()
    {
        // Arrange
        $user = User::factory()->create();
        $rule = FirewallRule::with('businessService')->first();

        Notification::fake();
        Http::fake([config('servicenow.uri') . '/*' => Http::sequence()
            ->pushStatus(408)
            ->push(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/request.json')), true))
        ]);

        // Act
        $response = CreateFirewallRequest::process($rule, $user);

        Notification::assertSentTo($user, CreateFirewallRequestNotification::class, 1);

        $this->assertEquals('Success', $response->json('result')['status']);
    }

    /** @test */
    public function bad_requests()
    {
        // Arrange
        $user = User::factory()->create();
        $rule = FirewallRule::with('businessService')->first();

        Notification::fake();
        Http::fake([config('servicenow.uri') . '/*' => Http::sequence()
            ->pushStatus(400)
        ]);

        // Act
        $response = CreateFirewallRequest::process($rule, $user);

        // Assert
        Notification::assertSentTo($user, CreateFirewallRequestNotification::class, 1);
        $this->assertEquals(400, $response->status());
    }

    /** @test */
    public function it_sends_a_mail_notification_to_the_user()
    {
        // Arrange
        $user = User::factory()->create();
        $rule = new FirewallRule;

        Notification::fake();
        Http::fake([config('servicenow.uri') . '/*' =>
            Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/request.json')), true))
        ]);

        // Act
        $response = CreateFirewallRequest::process($rule, $user);

        // Assert
        Notification::assertSentTo($user, CreateFirewallRequestNotification::class, 1);
        $this->assertStringContainsString($response->content(), 'Rule was not found!');
    }


    /** @test */
    public function only_the_first_attempt_to_delete_a_rule_files_a_snow_request()
    {
        $user1 = User::first();
        $user1->businessServices()->attach(
            BusinessService::whereName('LHG_AIREMCLOUD_P')->first()->id
        );

        $user2 = User::factory()->create();
        $user2->businessServices()->attach(
            BusinessService::whereName('LHG_AIREMCLOUD_P')->first()->id
        );

        $rule = $user1->firewallRules()
            ->where('pci_dss', 1)
            ->where('action', 'add')
            ->where('status', 'open')
            ->first();

        Notification::fake();

        Http::fake([config('servicenow.uri') . '/*' =>
            Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/request.json')), true))
        ]);

        Livewire::actingAs($user1)->test(FirewallRulesRead::class)
            ->set('rule', $rule)
            ->call('delete', $rule->id)
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved...', 'type' => 'success']);

        Notification::assertSentTo(
            $user1,
            function (CreateFirewallRequestNotification $notification) {
                return $notification->getBody() === [
                    "status" => "Success",
                    "requestNumber" => "REQ0032701",
                    "requestItemNumber" => "ROT0033162",
                ];
            }
        );

        Livewire::actingAs($user2)->test(FirewallRulesRead::class)
            ->set('rule', $rule)
            ->call('delete', $rule->id)
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved...', 'type' => 'success']);

        Notification::assertSentTo(
            $user2,
            function (CreateFirewallRequestNotification $notification) {
                return $notification->getBody() === __('messages.rule_previously_decommissioned');
            }
        );
    }

    protected function importRules()
    {
        Subnet::factory()->createMany([
            ['name' => '10.123.207.0', 'size' => 24],
            ['name' => '10.123.186.0', 'size' => 24],
            ['name' => '10.123.75.0', 'size' => 24],
        ]);

        Queue::fake(ImportBusinessServiceMemberJob::class);

        $fileContents = json_decode(file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/valid.json'), true);;

        foreach ($fileContents as $fileContent) {
            ImportFirewallRequestJob::dispatch(User::factory()->create(), $fileContent);
        }
    }
}