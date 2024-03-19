<?php

namespace Tests\Feature\API\ServiceNow;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateFirewallRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->username = config('servicenow.client_id');
        $this->password = config('servicenow.client_secret');
        $this->uri = config('servicenow.uri').'/api/delag/retrieve_cost_centers/CreateCatalogItem';
    }

    /** @test */
    public function create_a_firewall_rules_request_on_behalf_of_a_user(): void
    {
        Http::fake([
            'https://lhgroupuat.service-now.com/api/delag/retrieve_cost_centers/CreateCatalogItem' => Http::response(
                json_decode(file_get_contents(base_path('/tests/Feature/API/ServiceNow/Stubs/FirewallRequest.json')), true)
            ),
        ]);

        $payload = [
            'request_description' => 'Disable FW connection to Service B',
            'requestor_mail' => 'rafael.camison@austrian.com',
            'opened_by' => 'rafael.camison@austrian.com',
            'business_service' => 'LHG_SERVICENOW_P',
            'cost_center' => '000123',
            'rules' => [
                [
                    'action' => 'delete',
                    'type_destination' => 'ip_address_dest',
                    'destination' => '10.0.0.10/24',
                    'type_source' => 'ip_address_source',
                    'source' => '10.0.1.10',
                    'service' => 'tcp',
                    'destination_port' => '22',
                    'description' => 'A connection between service A and service B rule 1',
                    'pci_dss' => 'Yes',
                    'no_expiry' => 'No',
                    'nat_required' => 'No',
                    'application_id' => '',
                    'contact' => '',
                    'business_purpose' => 'disable FW connection between service A and service B',
                ],
                [
                    'action' => 'delete',
                    'type_destination' => 'ip_address_dest',
                    'destination' => '10.0.0.10/24',
                    'type_source' => 'ip_address_source',
                    'source' => '10.0.1.10',
                    'service' => 'tcp',
                    'destination_port' => '22',
                    'description' => 'A connection between service A and service B rule 1',
                    'pci_dss' => 'Yes',
                    'no_expiry' => 'No',
                    'nat_required' => 'No',
                    'application_id' => '',
                    'contact' => '',
                    'business_purpose' => 'disable FW connection between service A and service B',

                ],
            ],
        ];

        $response = Http::withBasicAuth($this->username, $this->password)
            ->post($this->uri, $payload);

        $this->assertEquals(200, $response->status());

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Success', $response->json('result')['status']);
        $this->assertEquals('REQ0135664', $response->json('result')['requestNumber']);
        $this->assertEquals('RITM0156145', $response->json('result')['requestItemNumber']);
        $this->assertEquals('https://lhgroupuat.service-now.com/sp?id=ticket&table=sc_request&sys_id=e715cee71b740e105c7e744c8b4bcb2c', $response->json('result')['requestNumberlink']);
        $this->assertEquals('https://lhgroupuat.service-now.com/sp?id=ticket&table=sc_req_item&sys_id=2315cee71b740e105c7e744c8b4bcb2d', $response->json('result')['requestItemNumberLink']);
    }
}
