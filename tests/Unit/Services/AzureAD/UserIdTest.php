<?php

namespace Tests\Unit\Services\AzureAD;

use App\Services\AzureAD\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserIdTest extends TestCase
{
    /** @test */
    public function it_must_be_a_valid_uuid()
    {
        $userId = new UserId('87d349ed-44d7-43e1-9a83-5f2406dee5bd');

        $this->assertEquals('87d349ed-44d7-43e1-9a83-5f2406dee5bd', $userId->get());
    }

    /** @test */
    public function invalid_uuid_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        new UserId('rafael.camison@austrian.com');
    }
}
