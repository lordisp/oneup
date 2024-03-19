<?php

namespace Tests\Feature\API\ServiceNow;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroupMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->username = config('servicenow.client_id');
        $this->password = config('servicenow.client_secret');
        $this->uri = config('servicenow.uri').'/api/delag/retrieve_cost_centers/GetGroupFromBsandType';
    }

    /** @test */
    public function api_returns_valid_email_array(): void
    {
        $response = Http::withBasicAuth($this->username, $this->password)->post($this->uri, [
            'names' => ['LHG_ONEUP_P'],
            'types' => ['Responsibles'],
        ]);

        $this->assertequals(200, $response->status());

        $results = $response->json('result');

        $this->assertIsArray($results);

        foreach ($results[0] as $result) {
            $this->assertIsString($result);
            $this->assertEquals($result, filter_var($result, FILTER_VALIDATE_EMAIL), "The email {$result} is not valid");
        }
    }

    /** @test */
    public function invalid_group_name_throws_http_exception(): void
    {
        $response = Http::withBasicAuth($this->username, $this->password)->post($this->uri, [
            'names' => ['LHG_ONEUP_P'],
            'types' => ['InvalidGroup'],
        ]);
        $json = $response->json();

        $this->assertEquals(
            'Payload must have only valid values like "EscalationNotification","Responsibles","SecurityContacts"',
            data_get($json, 'result.message')
        );
        $this->assertEquals(400, $response->status());
    }

    /** @test */
    public function api_returns_valid_email_array_with_all_types(): void
    {
        $response = Http::withBasicAuth($this->username, $this->password)->post($this->uri, [
            'names' => ['LHG_ONEUP_P'],
            'types' => ['EscalationNotification', 'Responsibles', 'SecurityContacts'],
        ]);

        $this->assertequals(200, $response->status());

        $results = $response->json('result');

        $this->assertIsArray($results);

        foreach ($results[0] as $result) {
            $this->assertIsString($result);
            $this->assertEquals($result, filter_var($result, FILTER_VALIDATE_EMAIL), "The email {$result} is not valid");
        }
    }

    /** @test */
    public function expect_400_for_invalid_business_services(): void
    {
        $response = Http::withBasicAuth($this->username, $this->password)->post($this->uri, [
            'names' => ['Invalid'],
            'types' => ['EscalationNotification', 'Responsibles', 'SecurityContacts'],
        ]);

        $this->assertEquals(
            'Payload must have valid Business service name',
            data_get($response->json(), 'result.message')
        );

        $this->assertEquals(400, $response->status());
    }
}
