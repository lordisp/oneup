<?php

namespace Tests\Unit\Services\AzureAD;

use App\Services\AzureAD\UserPrincipal;
use App\Services\AzureAD\UserProperties;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserPropertiesTest extends TestCase
{
    /** @test */
    public function it_must_be_a_valid_email_address()
    {
        $properties = new UserProperties('mail,displayName,companyName');

        $this->assertEquals('mail,displayName,companyName', $properties->get());
    }

    /** @test */
    public function invalid_user_principal_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        new UserProperties('mail,foo,bar');
    }
}
