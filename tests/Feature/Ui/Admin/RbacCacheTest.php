<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\Operation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacCacheTest extends TestCase
{
    use RefreshDatabase, WithRbacCache;

    /** @test */
    public function can_create_operation_by_trait(): void
    {
        $operation = 'foo/bar/baz/qux';
        $description = 'Awesome Foo';

        $cache = $this->updateOrCreate($operation, $description);
        $this->assertEquals($operation, $cache);

        $this->updateOrCreate($operation, $description);

        $this->assertDatabaseCount(Operation::class, 1);
    }

    /** @test */
    public function can_flush_cache_for_rbac(): void
    {
        $operation = 'foo/bar/baz';
        $description = 'Awesome Foo';

        $this->updateOrCreate($operation, $description);
        $this->flushRbacCache();

        $this->assertNull(cache()->tags('rbac')->get($operation));
        $this->assertDatabaseCount(Operation::class, 1);
    }

    /** @test */
    public function validation_throws_error_for_operation_1(): void
    {
        $this->expectExceptionMessage('The operation format is invalid.');

        $operation = 'foo/bar/baz/'; // operation must end with a-zA-Z
        $description = 'Awesome Foo';

        $this->updateOrCreate($operation, $description);

        $this->assertDatabaseCount(Operation::class, 0);

    }

    /** @test */
    public function validation_throws_error_for_operation_2(): void
    {
        $this->expectExceptionMessage('The operation format is invalid.');

        $operation = 'foo/bar-baz'; // operation must end with a-z
        $description = 'Awesome Foo';

        $this->updateOrCreate($operation, $description);
        $this->assertDatabaseCount(Operation::class, 0);
    }

    /** @test */
    public function validation_throws_error_for_operation_3(): void
    {
        $this->expectExceptionMessage('The operation format is invalid.');

        $operation = '/foo/bar/baz'; // operation must end with a-z
        $description = 'Awesome Foo';

        $this->updateOrCreate($operation, $description);
    }

    /** @test */
    public function validation_throws_error_for_description_1(): void
    {
        $this->expectExceptionMessage('The description must be at least 4 characters.');

        $operation = 'foo/bar/baz';
        $description = 'Foo';

        $this->updateOrCreate($operation, $description);
        $this->assertDatabaseCount(Operation::class, 0);
    }

    /** @test */
    public function validation_throws_error_for_description_2(): void
    {
        $this->expectExceptionMessage('The description field is required.');

        $operation = 'foo/bar/baz';
        $description = '';

        $this->updateOrCreate($operation, $description);
        $this->assertDatabaseCount(Operation::class, 0);
    }
}
