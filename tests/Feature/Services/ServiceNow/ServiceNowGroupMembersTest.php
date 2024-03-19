<?php

namespace Tests\Feature\Services\ServiceNow;

use App\Services\ServiceNow\GroupMembers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceNowGroupMembersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->flush();
    }

    /** @test */
    public function returns_array_of_email_addresses_from_one_business_service()
    {
        $members = (new GroupMembers('LHG_AZUREFOUNDATION_P', 'Responsibles'))->handle();

        foreach ($members as $member) {
            $this->assertIsString($member);
        }
    }

    /** @test */
    public function it_returns_unique_email_addresses_from_more_business_service()
    {
        $members = (new GroupMembers(['LHG_SOAR_T', 'LHG_GAC_P', 'LHG_AZUREFOUNDATION_P'], 'Responsibles'))->handle();

        foreach ($members as $member) {
            $this->assertIsString($member);
        }
    }

    /** @test */
    public function returns_array_of_same_emails_from_api_and_cache()
    {
        $response = (new GroupMembers('LHG_GAC_P', 'Responsibles'))->handle();

        $cache = cache()->get('responsibles_lhg_gac_p');

        $this->assertIsArray($response);

        $this->assertEquals($response, $cache);

    }

    /** @test */
    public function it_trims_inactive_business_service_names()
    {
        cache()->flush();

        $response = (new GroupMembers(['LHG_SOAR_T [Non-Operational]', 'LHG_GAC_P', 'LHG_AZUREFOUNDATION_P'], 'Responsibles'))->handle();

        $cache = cache()->get('responsibles_lhg_azurefoundation_p_lhg_gac_p_lhg_soar_t');

        $this->assertIsArray($response);

        $this->assertEquals($response, $cache);
    }

    /** @test */
    public function invalid_group_types_throw_an_http_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new GroupMembers('LHG_AZUREFOUNDATION_P', 'InvalidGroupType'))->handle();
    }
}
