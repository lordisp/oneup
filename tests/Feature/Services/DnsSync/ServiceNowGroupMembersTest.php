<?php

namespace Tests\Feature\Services\DnsSync;

use App\Services\ServiceNow\GroupMembers;
use Tests\TestCase;


class ServiceNowGroupMembersTest extends TestCase
{

    /** @test */
    public function it_returns_an_array_of_emails_from_the_api_and_from_cache()
    {
        $response = (new GroupMembers('LHG_GAC_P', 'Responsibles', 250))->handle();

        $cache = cache()->get('responsibles_lhg_gac_p');

        $this->assertIsArray($response);

        $this->assertEquals($response, $cache);

    }
}
