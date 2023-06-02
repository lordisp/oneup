<?php

namespace Tests\Feature\Logger;

use App\Models\LogMessage;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DatabaseLoggerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_log_entry_to_the_database()
    {
        Log::info('Info Log');

        $this->assertDatabaseCount(LogMessage::class, 1);
    }

    /** @test */
    public function it_creates_a_debug_log_entry_to_the_database()
    {
        Log::debug('Debug Log', ['context' => 'debug']);

        $this->assertEquals('Debug Log', LogMessage::first()->message);
        $this->assertEquals(100, LogMessage::first()->level);
        $this->assertEquals('Debug', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'debug'], LogMessage::first()->context);
    }

    /** @test */
    public function it_creates_an_info_log_entry_to_the_database()
    {
        Log::info('Info Log', ['context' => 'info']);

        $this->assertEquals('Info Log', LogMessage::first()->message);
        $this->assertEquals(200, LogMessage::first()->level);
        $this->assertEquals('Info', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'info'], LogMessage::first()->context);
    }

    /** @test */

    public function it_creates_a_notice_log_entry_to_the_database()
    {
        Log::notice('Notice Log', ['context' => 'notice']);

        $this->assertEquals('Notice Log', LogMessage::first()->message);
        $this->assertEquals(250, LogMessage::first()->level);
        $this->assertEquals('Notice', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'notice'], LogMessage::first()->context);
    }

    /** @test */
    public function it_creates_a_warning_log_entry_to_the_database()
    {
        Log::warning('Waring Log', ['context' => 'warning']);

        $this->assertEquals('Waring Log', LogMessage::first()->message);
        $this->assertEquals(300, LogMessage::first()->level);
        $this->assertEquals('Warning', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'warning'], LogMessage::first()->context);
    }

    /** @test */
    public function it_creates_an_error_log_entry_to_the_database()
    {
        Log::error('Error Log', ['context' => 'error']);

        $this->assertEquals('Error Log', LogMessage::first()->message);
        $this->assertEquals(400, LogMessage::first()->level);
        $this->assertEquals('Error', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'error'], LogMessage::first()->context);
    }

    /** @test */
    public function it_creates_an_critical_log_entry_to_the_database()
    {
        Log::critical('Critical Log', ['context' => 'critical']);

        $this->assertEquals('Critical Log', LogMessage::first()->message);
        $this->assertEquals(500, LogMessage::first()->level);
        $this->assertEquals('Critical', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'critical'], LogMessage::first()->context);
    }

    /** @test */
    public function it_creates_an_alert_log_entry_to_the_database()
    {
        Log::alert('Alert Log', ['context' => 'alert']);

        $this->assertEquals('Alert Log', LogMessage::first()->message);
        $this->assertEquals(550, LogMessage::first()->level);
        $this->assertEquals('Alert', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'alert'], LogMessage::first()->context);
    }

    /** @test */
    public function it_creates_an_emergency_log_entry_to_the_database()
    {
        Log::emergency('Emergency Log', ['context' => 'emergency']);

        $this->assertEquals('Emergency Log', LogMessage::first()->message);
        $this->assertEquals(600, LogMessage::first()->level);
        $this->assertEquals('Emergency', LogMessage::first()->level_name);
        $this->assertEquals(['context' => 'emergency'], LogMessage::first()->context);
    }

    /** @test */
    public function it_logs_exceptions_as_error_to_the_database()
    {
        report(new Exception('This exception should be logged.'));

        $this->assertDatabaseCount(LogMessage::class, 1);
    }

    /** @test */
    public function a_database_entry_can_be_soft_deleted()
    {
        Log::info('Info Log');

        LogMessage::first()->delete();

        $this->assertCount(1, LogMessage::onlyTrashed()->get());
    }

    /** @test */
    public function a_database_entry_can_be_restored()
    {
        Log::info('Info Log');

        $logMessage = LogMessage::first();

        $logMessage->delete();

        $logMessage->restore();

        $this->assertDatabaseCount(LogMessage::class, 1);
    }

    /** @test */
    public function it_deletes_all_soft_deleted_entries()
    {
        Log::info('Info Log');
        Log::debug('Debug Log');

        LogMessage::get()->map->delete();

        $this->assertDatabaseCount(LogMessage::class, 2);

        LogMessage::withTrashed()->get()->map->forceDelete();

        $this->assertDatabaseCount(LogMessage::class, 0);
    }
}
