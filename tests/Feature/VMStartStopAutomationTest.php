<?php

namespace Tests\Feature;

use App\Events\VmStateChangeEvent;
use App\Jobs\VmStartStopProcess;
use App\Jobs\VmStartStopSchedulerJob;
use App\Traits\Token;
use Arr;
use Database\Seeders\TokenCacheProviderSeeder;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Tests\TestCase;

class VMStartStopAutomationTest extends TestCase
{
    use RefreshDatabase, Token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TokenCacheProviderSeeder::class]);
    }

    /** @test */
    public function can_read_data_from_share_point()
    {
        $values = Http::withToken(decrypt($this->newToken('lhg_graph')))
            ->get('https://graph.microsoft.com/v1.0/sites/lufthansagroup.sharepoint.com/drives/b!-wUp0h0GOEiIJXb9iEfdAikgMp-EVrBJig5eJNEqyUFv1u2jjdV_QKywhUjwFX3F/items/01K2ZHOAECXE3XURD4SRDZUOVPJDHIU4LI/workbook/worksheets/scheduler/usedRange')
            ->json('values');

        $this->assertIsArray($values);
        $first = Arr::first($values);
        $this->assertEquals('Name', $first[0]);
        $this->assertEquals('Subscription', $first[1]);
        $this->assertEquals('Start', $first[2]);
        $this->assertEquals('Stop', $first[3]);
        $this->assertEquals('WeekType', $first[4]);
        $this->assertEquals('Status', $first[5]);
    }

    /** @test */
    public function request_retries_on_too_many_requests()
    {
        Queue::fake([VmStartStopProcess::class]);

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/lufthansagroup.sharepoint.com/*' => Http::sequence()
                ->push('Too Many Requests', 429, ['Retry-After' => '0'])
                ->push(json_decode(
                    file_get_contents(
                        base_path('/tests/Feature/Stubs/VmStartStop/sharepoint-serverlist.json')
                    ), true
                )),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Queue::assertPushed(VmStartStopProcess::class, 3);
    }

    /** @test */
    public function request_retries_unauthenticated()
    {
        Queue::fake([VmStartStopProcess::class]);

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/lufthansagroup.sharepoint.com/*' => Http::sequence()
                ->push(status: 401)
                ->push(json_decode(
                    file_get_contents(
                        base_path('/tests/Feature/Stubs/VmStartStop/sharepoint-serverlist.json')
                    ), true
                )),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Queue::assertPushed(VmStartStopProcess::class, 3);
    }

    /** @test */
    public function request_retries_unauthorized()
    {
        Queue::fake([VmStartStopProcess::class]);

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/lufthansagroup.sharepoint.com/*' => Http::sequence()
                ->push(status: 403)
                ->push(json_decode(
                    file_get_contents(
                        base_path('/tests/Feature/Stubs/VmStartStop/sharepoint-serverlist.json')
                    ), true
                )),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Queue::assertPushed(VmStartStopProcess::class, 3);
    }

    /** @test */
    public function sharepoint_request_failed()
    {
        Queue::fake([VmStartStopProcess::class]);
        Log::shouldReceive('error')->once();

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/lufthansagroup.sharepoint.com/*' => Http::response(status: 400),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Queue::assertPushed(VmStartStopProcess::class, 0);
    }

    /** @test */
    public function it_queues_the_vm_start_stop_process()
    {
        Queue::fake([VmStartStopProcess::class]);
        Http::fake([
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Queue::assertPushed(VmStartStopProcess::class, 3);

        $this->assertTrue(true);
    }

    /** @test */
    public function vm_state_change_event_throws_invalid_argument_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(__('messages.failed.invalid_operation_argument'));

        event(new VmStateChangeEvent('invalid', 'start', 'vmName'));
    }

    /** @test */
    public function a_server_not_found_warning_will_be_logged()
    {
        Event::fake([VmStateChangeEvent::class]);
        Log::shouldReceive('warning')->twice();

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(2, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => Http::response(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertNotDispatched(VmStateChangeEvent::class);
    }

    /** @test */
    public function invalid_provisioning_start_state_will_be_logged()
    {
        Event::fake([VmStateChangeEvent::class]);
        Log::shouldReceive('error')->between(4, 4);

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(15, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->startingStartGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertNotDispatched(VmStateChangeEvent::class);
    }

    /** @test */
    public function invalid_provisioning_deallocation_state_will_be_logged()
    {
        Event::fake([VmStateChangeEvent::class]);
        Log::shouldReceive('error')->between(4, 4);

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(2, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->startingDeallocatedGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertNotDispatched(VmStateChangeEvent::class);
    }

    /** @test */
    public function vm_state_change_event_deallocate_server()
    {
        Event::fake([VmStateChangeEvent::class]);
        Log::shouldReceive('info')->once();

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(2, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->runningGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertDispatched(VmStateChangeEvent::class, function ($event) {
            return $event->operation == 'deallocate';
        });
    }

    /** @test */
    public function vm_state_change_event_start_server()
    {
        Event::fake([VmStateChangeEvent::class]);
        Log::shouldReceive('info')->once();

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(15, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->deallocatedGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertDispatched(VmStateChangeEvent::class, function ($event) {
            return $event->operation == 'start';
        });
    }

    /** @test */
    public function vm_state_change_event_deallocate_a_stopped_server()
    {
        Event::fake([VmStateChangeEvent::class]);
        Log::shouldReceive('info')->once();

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(2, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->stoppedGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertDispatched(VmStateChangeEvent::class, function ($event) {
            return $event->operation == 'deallocate';
        });
    }

    /** @test */
    public function vm_state_change_event_skips_a_deallocated_server_for_deallocation()
    {
        Event::fake([VmStateChangeEvent::class]);

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(2, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->deallocatedGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertNotDispatched(VmStateChangeEvent::class);
    }

    /** @test */
    public function vm_state_change_event_skips_a_starting_server_for_deallocation()
    {
        Event::fake([VmStateChangeEvent::class]);

        Carbon::setTestNow(
            Carbon::today()
                ->setTime(15, 0)
                ->setDate(2023, 9, 1) //Friday
        );

        Http::fake([
            'https://login.microsoftonline.com/*' => $this->accessTokenFaker(),
            'https://graph.microsoft.com/v1.0/sites/*' => $this->serverlistFaker(),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => $this->startingGraphFaker(),
        ]);

        VmStartStopSchedulerJob::dispatch();

        Event::assertNotDispatched(VmStateChangeEvent::class);
    }

    /** @test */
    public function run_a_full_event()
    {
        Log::shouldReceive('error')->atMost();
        Log::shouldReceive('warning')->atMost();
        Log::shouldReceive('info')->atLeast(1);

        VmStartStopSchedulerJob::dispatch();

        $this->assertTrue(true);
    }

    /** @test */
    public function start_top_scheduler_runs_every_fifteen_minutes()
    {
        $schedule = app()->make(Schedule::class);

        $events = collect($schedule->events())->filter(function (\Illuminate\Console\Scheduling\Event $event) {
            return stripos($event->description, 'VmStartStopSchedulerJob');
        });

        if ($events->count() == 0) {
            $this->fail('No events found');
        }

        $events->each(function (CallbackEvent $event) {
            $this->assertEquals('*/15 * * * *', $event->expression);
        });
    }

    protected function runningGraphFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/rg_l-jump-p01-graph-running.json')
                ), true
            )
        );
    }

    protected function deallocatedGraphFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/rg_l-jump-p01-graph-deallocated.json')
                ), true
            )
        );
    }

    protected function stoppedGraphFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/rg_l-jump-p01-graph-stopped.json')
                ), true
            )
        );
    }

    protected function startingGraphFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/rg_l-jump-p01-graph-starting.json')
                ), true
            )
        );
    }

    protected function startingDeallocatedGraphFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/rg_l-jump-p01-graph-starting-deallocated.json')
                ), true
            )
        );
    }

    protected function startingStartGraphFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/rg_l-jump-p01-graph-starting-start.json')
                ), true
            )
        );
    }

    protected function serverlistFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(
                file_get_contents(
                    base_path('/tests/Feature/Stubs/VmStartStop/sharepoint-serverlist.json')
                ), true
            )
        );
    }

    protected function accessTokenFaker(): PromiseInterface
    {
        return Http::response(
            json_decode(file_get_contents(base_path('/tests/Feature/Stubs/oidc_access_token.json')), true)
        );
    }
}
