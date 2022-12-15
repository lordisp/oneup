<?php

namespace Tests\Feature\Database;

use App\Traits\ValidationRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImportFirewallTemplateTest extends TestCase
{
    use RefreshDatabase, ValidationRules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = json_decode(file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/valid_1.json'), true);
    }


    /** @test */
    public function load_firewall_export_file_into_array()
    {
        $this->assertIsArray($this->file);

        $requiredFields = [
            'Template', 'request_description', 'rules', 'RequestorMail', 'RequestorFirstName', 'RequestorLastName', 'RequestorUID', 'RITMNumber', 'opened_by', 'Subject',
        ];

        $ruleFields = [
            'action', 'type_destination', 'destination', 'type_source', 'source', 'service', 'description', 'no_expiry', 'end_date', 'pci_dss', 'nat_required', 'application_id', 'contact', 'business_purpose',
        ];

        foreach ($requiredFields as $requiredField) {
            foreach ($this->file as $item) {
                $this->assertArrayHasKey($requiredField, $item);
            }
        }

        foreach ($ruleFields as $requiredField) {
            foreach ($this->file as $item) {
                foreach (data_get($item, 'rules') as $rule)
                    $this->assertArrayHasKey($requiredField, $rule);
            }
        }
    }

    protected function validateJson(array $array)
    {
        try {
            return $this->firewallValidation($array)->validate();
        } catch (ValidationException $exception) {
            $this->assertEmpty($exception);
        }

    }

    /** @test */
    public function test_validator()
    {
        foreach ($this->file as $array) {
            $validated = $this->validateJson($array);
            $this->assertIsArray($validated);
        }
    }
}
