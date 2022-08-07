<?php

namespace Tests\Feature\Auth;

use App\Http\Livewire\Auth\ShowUsers as ShowUsersComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowUsers extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function can_see_livewire_component()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ShowUsersComponent::class)
            ->assertViewIs('livewire.auth.show-users');
    }
}
