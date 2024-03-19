<?php

namespace Tests\Feature;

use App\Console\Kernel;
use App\Jobs\ServiceNow\UpdateUsersBasedOnBusinessServiceScheduler;
use App\Models\BusinessService;
use App\Models\User;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateUsersBasedOnBusinessServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            TokenCacheProviderSeeder::class,
        ]);
    }

    /** @test */
    public function can_add_audit_log_for_user(): void
    {
        $user = User::factory()->create();

        $user->audits()->create([
            'actor' => 'system',
            'activity' => 'event',
            'status' => 'Success',
        ]);

        $this->assertDatabaseHas('audits', [
            'actor' => 'system',
            'activity' => 'event',
            'status' => 'Success',
        ]);
    }

    /** @test */
    public function it_remove_user_from_business_service(): void
    {
        $fake = json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true);
        Http::fake([config('servicenow.uri').'/*' => Http::response($fake)]);

        $businessService = BusinessService::factory()->create();
        $userEmails = Arr::flatten($fake['result']);

        foreach ($userEmails as $email) {
            $user = User::factory()->create(['email' => $email]);
            $businessService->users()->attach($user->id);
        }

        $user = User::factory()->create();
        $businessService->users()->attach($user->id);

        UpdateUsersBasedOnBusinessServiceScheduler::dispatch(BusinessService::all());

        $this->assertDatabaseHas('audits', [
            'actor' => 'UpdateUsersBasedOnBusinessServiceJob',
            'activity' => 'Removed Business-Service Members',
            'status' => 'Success',
        ]);
    }

    /** @test */
    public function it_adds_user_to_business_service(): void
    {
        $businessServices = BusinessService::factory()
            ->hasUsers(3)
            ->count(3)
            ->create();

        $businessServices->each(function ($businessService) {
            $businessService->users->each->delete();
        });

        UpdateUsersBasedOnBusinessServiceScheduler::dispatch($businessServices);

        $this->assertDatabaseHas('audits', [
            'actor' => 'Scim',
            'activity' => 'Added Business-Service Members',
            'status' => 'Success',
        ]);
    }

    /** @test */
    public function job_scheduled_run_every_ten_minutes(): void
    {
        $this->app->make(Kernel::class)->bootstrap();
        $events = $this->app[Schedule::class]->events();

        $classnames = [
            UpdateUsersBasedOnBusinessServiceScheduler::class,
        ];
        foreach ($classnames as $classname) {
            $this->assertTrue(
                collect($events)->contains(function ($value) use ($classname) {
                    return $value->description === $classname || str_contains($value->description, $classname)
                        && $value->expression === '*/10 * * * *';
                }),
                "{$classname} is not scheduled"
            );
        }
    }
}
