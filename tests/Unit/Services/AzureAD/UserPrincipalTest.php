<?php

namespace Tests\Unit\Services\AzureAD;

use App\Services\AzureAD\UserPrincipal;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserPrincipalTest extends TestCase
{
    /** @test */
    public function it_must_be_a_valid_email_address()
    {
        $userPrincipal = new UserPrincipal('rafael.camison@austrian.com');

        $this->assertEquals('rafael.camison@austrian.com', $userPrincipal->get());
    }

    /** @test */
    public function invalid_user_principal_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        new UserPrincipal('someString');
    }
}
