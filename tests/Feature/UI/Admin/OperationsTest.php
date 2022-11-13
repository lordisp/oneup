<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\Operations;
use App\Models\Operation;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;

class OperationsTest extends TestCase implements FrontendTest
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([OperationSeeder::class, UserAzureSeeder::class]);
    }

    /** @test */
    public function cannot_access_route_as_guest()
    {
        $this->get('/admin/operations')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user()
    {
        $this->actingAs(User::first())
            ->get('/admin/operations')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component()
    {
        Livewire::actingAs(User::first())->test(Operations::class)->assertOk();
    }

    /** @test */
    public function can_view_component()
    {
        Operation::truncate();
        $this->actingAs(User::first())
            ->get('/admin/operations')
            ->assertSeeLivewire('admin.operations')
            ->assertSee(__('button.operation_create'))
            ->assertSee(__('empty-table.admin_provider', ['attribute' => 'operations']));
    }

    /** @test */
    public function can_create_new_operation()
    {
        $this->assertDatabaseCount(Operation::class, 10);
        $operation = Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('operation.operation', 'some/test/operation')
            ->set('operation.description', 'some test operation')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'create'])
            ->get('operation');
        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertDatabaseCount(Operation::class, 11);
    }

    /** @test */
    public function create_new_operation_has_errors()
    {
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('operation.operation', Str::random(4))
            ->set('operation.description', Str::random(4))
            ->call('save')
            ->assertHasErrors([
                'operation.operation' => ['min'],
                'operation.description' => ['min'],
            ]);
    }

    /** @test */
    public function can_delete_a_operation()
    {
        $this->assertDatabaseCount(Operation::class, 10);
        $operationId = Operation::first()->id;

        Log::shouldReceive('info')->once()->withArgs(function ($message) {
            return str_contains($message, 'Destroy Operation') == true;
        });

        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->call('deleteModal', $operationId)
            ->assertCount('objects', 1)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->call('deleteOperation')
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.deleted'), 'type' => 'success'])
            ->assertPayloadSet('selected', [])
            ->assertPayloadSet('selectedPage', false)
            ->assertPayloadSet('selectAll', false);
        $this->assertDatabaseCount(Operation::class, 9);
    }

    /** @test */
    public function can_edit_a_operation()
    {
        $operation = Operation::first();
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->call('editModal', $operation)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->assertSee($operation->operation)
            ->assertSee($operation->description)
            ->assertHasNoErrors()
            ->call('save')
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved', 'type' => 'success'])
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit']);
    }

    /** @test */
    public function test_search_bar()
    {
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('search', 'OneUp')
            ->assertSee('OneUp')
            ->assertDontSee('Azure.Resource')
            ->set('search', 'Azure.Resource')
            ->assertSee('Azure.Resource')
            ->assertDontSee('OneUp')
            ->call('clearSearch')
            ->assertCount('queryRows', 10);
    }

    /** @test */
    public function test_pagination()
    {
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('perPage', 6)
            ->assertSee('Showing 6 of 10')
            ->assertSee('Next')
            ->assertDontSee(__('pagination.previous'))
            ->call('gotoPage', 2)
            ->assertSee(__('pagination.previous'))
            ->assertSee('Showing 4 of 10');
    }
}
