<?php

namespace Tests\Feature\Logger;

use App\Jobs\Logger\DatabaseCleanerJob;
use App\Models\LogMessage;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ClearCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_the_cleaner_job()
    {
        Queue::fake();

        report(new Exception('This exception should be logged.'));

        $this->artisan("logs:clear --level=error --age=25.3.2018 --job");

        Queue::assertPushed(DatabaseCleanerJob::class, 1);

    }

    /** @test */
    public function it_can_clear_logs_from_database_as_job()
    {
        LogMessage::factory()->deleted()->level(400)->count(5)->create();

        $age = now()->subSecond()->toDateString();

        $this->artisan("logs:clear --level=error --age={$age} --job");

        $this->artisan('queue:work --once');

        $this->assertDatabaseCount(LogMessage::class, 0);
    }

    /** @test */
    public function it_can_clear_all_logs_from_database_as_job()
    {
        LogMessage::factory()->count(10)->create();

        $this->artisan("logs:clear --job");

        $this->artisan('queue:work --once');

        $this->assertDatabaseCount(LogMessage::class, 0);
    }


    public function it_can_clear_logs_from_database()
    {
        LogMessage::factory()->deleted()->level(400)->count(10)->create();

        $this->assertDatabaseCount(LogMessage::class, 10);

        $this->artisan("logs:clear --level=error --age=25.03.2020");

        $this->assertDatabaseCount(LogMessage::class, 0);
    }

}
