<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Tests\Feature\Models;

use App\Models\Operation;
use App\Models\Role;
use Database\Seeders\OperationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_operation()
    {
        $this->assertDatabaseCount(Operation::class, 0);

        $operation = Operation::factory()->create();

        $this->assertDatabaseCount(Operation::class, 1);

        $this->assertTrue(Str::isUuid($operation->first()->id));
        $this->assertIsString($operation->first()->operation);
        $this->assertIsString($operation->first()->slug);
        $this->assertIsString($operation->first()->description);
    }

    /** @test */
    public function can_search_for_operations()
    {
        $this->seed(OperationSeeder::class);

        $results = Operation::search('');

        $this->assertNotEmpty($results->first()->operation);
    }

    /** @test */
    public function can_list_related_roles()
    {
        $this->assertEquals(
            expected: Role::factory()->withOperations()->create()->name,
            actual: Operation::first()->roles()->first()->name
        );
    }
}
