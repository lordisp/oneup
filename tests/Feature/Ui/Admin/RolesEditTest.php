<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\Roles;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Helper;
use Tests\TestCase;

class RolesEditTest extends TestCase
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserAzureSeeder::class,OperationSeeder::class,RoleSeeder::class]);
    }

    /** @test */
    public function can_search_for_roles()
    {
        $user = User::first();
        $user->assignRole('Roles administrator');

        Role::factory()->state(['name'=>'test1', 'description'=>'description1'])->create();
        Role::factory()->state(['name'=>'test2', 'description'=>'description2'])->create();

        Livewire::actingAs($user)
            ->test(Roles::class)
            ->call('clearSearch')
            ->assertSee('test1')
            ->assertSee('description1')
            ->assertSee('test2')
            ->assertSee('description2')
            ->set('search','test1')
            ->assertSee('test1')
            ->assertSee('description1')
            ->assertDontSee('test2')
            ->assertDontSee('description2')
            ->set('search','test2')
            ->assertSee('test2')
            ->assertSee('description2')
            ->assertDontSee('test1')
            ->assertDontSee('description1');
    }
}
