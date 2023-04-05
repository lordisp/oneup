<?php

namespace Tests\Feature\Logger;

use App\Logger\DatabaseCleaner;
use App\Models\LogMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class DatabaseCleanerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_force_delete_all_debug_logs_at_a_given_age()
    {
        LogMessage::factory()->level(100)->deleted()->create();

        $databaseCleaner = DatabaseCleaner::forceDelete()
            ->level('DEBUG')
            ->age(now()->subDay())
            ->run();

        $this->assertGreaterThan(0, $databaseCleaner);
        $this->assertDatabaseCount(LogMessage::class, 0);
    }

    /** @test */
    public function it_force_delete_all_logs_at_a_given_age()
    {
        LogMessage::factory()->deleted()->count(10)->create();

        $databaseCleaner = DatabaseCleaner::forceDelete()
            ->level('*')
            ->age(now()->subMonth())
            ->run();

        $this->assertGreaterThan(0, $databaseCleaner);
        $this->assertDatabaseCount(LogMessage::class, 0);
    }

    /** @test */
    public function an_invalid_level_name_throws_an_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level!');

        DatabaseCleaner::forceDelete()
            ->level('FOO')
            ->age(now()->subDay())
            ->run();
    }

    /** @test */
    public function an_invalid_age_throws_an_exception()
    {

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Age can not be in the future');

        DatabaseCleaner::forceDelete()
            ->level('DEBUG')
            ->age(now()->addDay())
            ->run();

        $this->assertDatabaseCount(LogMessage::class, 0);
    }
}
