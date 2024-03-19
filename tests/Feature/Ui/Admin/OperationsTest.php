<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\Operations;
use App\Models\Operation;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
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
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserAzureSeeder::class, OperationSeeder::class, RoleSeeder::class]);
    }

    /** @test */
    public function cannot_access_route_as_guest()
    {
        $this->get('/admin/operations')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user()
    {
        User::first()->assignRole('Operations reader');
        $this->actingAs(User::first())
            ->get('/admin/operations')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component()
    {
        User::first()->assignRole('Operations reader');
        Livewire::actingAs(User::first())->test(Operations::class)->assertOk();
    }

    /** @test */
    public function can_view_component()
    {
        User::first()->assignRole('Operations reader');
        $this->actingAs(User::first())
            ->get('/admin/operations')
            ->assertSeeLivewire('admin.operations')
            ->assertSee(__('button.operation_create'))
            ->assertDontSee(__('empty-table.admin_provider', ['attribute' => 'operations']));
    }

    /** @test */
    public function can_create_new_operation()
    {
        User::first()->assignRole('Operations operator');
        $this->assertDatabaseCount(Operation::class, 26);
        $operation = Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('operation.operation', 'some/test/operation')
            ->set('operation.description', 'some test operation')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'create'])
            ->get('operation');
        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertDatabaseCount(Operation::class, 27);
    }

    /** @test */
    public function create_new_operation_has_errors()
    {
        User::first()->assignRole('Operations operator');
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
    public function reader_cannot_edit_or_create_operations()
    {
        User::first()->assignRole('Operations reader');
        User::first()->unassignRole('Global Administrator');
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('operation.operation', Str::random(4))
            ->set('operation.description', Str::random(4))
            ->call('save')
            ->assertStatus(403);
    }

    /** @test */
    public function can_delete_a_operation()
    {
        $this->assertDatabaseCount(Operation::class, 26);
        $operationId = Operation::whereOperation('admin/tokenCacheProvider/read')->first()->id;

        Log::shouldReceive('info')->once()->withArgs(function ($message) {
            return str_contains($message, 'Destroy Operation') == true;
        });

        User::first()->assignRole('Operations Administrator');
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->call('deleteModal', $operationId)
            ->assertCount('objects', 1)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->call('deleteOperation')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.deleted'), 'type' => 'success'])
            ->assertPayloadSet('selected', [])
            ->assertPayloadSet('selectedPage', false)
            ->assertPayloadSet('selectAll', false);
        $this->assertDatabaseCount(Operation::class, 25);
    }

    /** @test */
    public function can_edit_a_operation()
    {
        $operation = Operation::where('operation', 'like', '%token%')->first();
        User::first()->assignRole('Operations operator');
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->call('editModal', $operation)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->assertSee($operation->operation)
            ->assertSee($operation->description)
            ->set('operation.operation', Str::random(10))
            ->set('operation.description', Str::random(10))
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit'])
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved', 'type' => 'success']);
    }

    /** @test */
    public function test_search_bar()
    {
        User::first()->assignRole('Operations reader');
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('search', 'operations')
            ->assertSee('operations')
            ->assertDontSee('tokenCacheProvider')
            ->set('search', 'tokenCacheProvider')
            ->assertSee('tokenCacheProvider')
            ->assertDontSee('operations')
            ->call('clearSearch')
            ->assertCount('queryRows', 15);
    }

    /** @test */
    public function test_pagination()
    {
        User::first()->assignRole('Operations reader');
        Livewire::actingAs(User::first())
            ->test(Operations::class)
            ->set('perPage', 10)
            ->assertSee('Showing 10 of 26')
            ->assertSee('Next')
            ->assertDontSee(__('pagination.previous'))
            ->call('gotoPage', 2)
            ->assertSee(__('pagination.previous'))
            ->assertSee('Showing 10 of 26');
    }
}
