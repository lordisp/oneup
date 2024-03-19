<?php

namespace Tests\Feature\Services\ServiceNow;

use App\Services\ServiceNow\FirewallRequestValidation;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FirewallRulesTest extends TestCase
{
    /** @test */
    public function it_must_contain_at_least_one_valid_rule(): void
    {
        $request = [
            'request_description' => 'Some useful description',
            'requestor_mail' => 'rafael.camison@austrian.com',
            'opened_by' => 'rafael.camison@austrian.com',
            'business_service' => 'required|string',
            'cost_center' => '123456789',
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
            ],
        ];

        $validated = new FirewallRequestValidation($request);

        $this->assertEquals($request, $validated->get()->validate());
    }

    /** @test */
    public function invalid_content_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        (new FirewallRequestValidation([]))->get()->validate();
    }
}
