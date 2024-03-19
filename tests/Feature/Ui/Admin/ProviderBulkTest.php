<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\Provider;
use App\Models\TokenCacheProvider;
use App\Models\User;
use Database\Seeders\GlobalAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\BulkTest;
use Tests\TestCase;

class ProviderBulkTest extends TestCase implements BulkTest
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(GlobalAdminSeeder::class);

        $this->user = User::factory()->create();

        $this->user->assignRole('Global Administrator');

        TokenCacheProvider::factory()->count(16)->create();

        $this->actingAs($this->user);

    }

    /** @test */
    public function can_select_one_or_more_rows()
    {
        $selected = TokenCacheProvider::take(2)->get()->toArray();

        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selected', [$selected[0]])
            ->assertCount('selected', 1)
            ->assertPayloadNotSet('selectPage', true)
            ->assertPayloadNotSet('selectAll', true)
            ->assertPayloadNotSet('selectPagePopup', true)
            ->set('selected', $selected)
            ->assertCount('selected', 2)
            ->assertCount('queryRows', 15);
    }

    /** @test */
    public function can_unselect_one_or_more_rows()
    {
        $selected = TokenCacheProvider::take(13)->get()->pluck('id')->toArray();
        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selectPage', true)
            ->assertCount('selected', 15)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadSet('selectAll', false)
            ->assertPayloadSet('selectPagePopup', true)
            ->set('selected', $selected)
            ->assertPayloadSet('selectPage', false)
            ->assertPayloadSet('selectAll', false)
            ->assertPayloadSet('selectPagePopup', false)
            ->assertCount('selected', 13);
    }

    /** @test */
    public function can_select_all_rows_on_first_page()
    {
        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->assertPayloadSet('selectPage', false)
            ->set('selectPage', true)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadNotSet('selectAll', true)
            ->assertPayloadSet('selectPagePopup', true)
            ->assertPayloadSet('page', 1)
            ->assertPayloadSet('perPage', 15)
            ->assertCount('queryRows', 15);
    }

    /** @test */
    public function can_unselect_all_raws_on_first_page()
    {
        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->assertPayloadNotSet('selectPage', true)
            ->assertPayloadNotSet('selectAll', true)
            ->assertPayloadNotSet('selectPagePopup', true)
            ->set('selectPage', true)
            ->assertCount('selected', 15)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadSet('selectAll', false)
            ->assertPayloadSet('selectPagePopup', true)
            ->set('selectPage', false)
            ->assertPayloadSet('selectPage', false)
            ->assertPayloadSet('selectAll', false)
            ->assertPayloadSet('selectPagePopup', false)
            ->assertCount('selected', 0);
    }

    /** @test */
    public function can_select_all_rows_on_second_page()
    {
        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selectPage', true)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadNotSet('selectAll', true)
            ->assertPayloadSet('selectPagePopup', true)
            ->assertCount('selected', 15)
            ->call('selectAll')
            ->assertCount('selected', 16)
            ->assertPayloadSet('selectPagePopup', false)
            ->assertPayloadSet('selectPage', true)
            ->call('gotoPage', 2)
            ->assertPayloadSet('page', 2)
            ->assertPayloadSet('perPage', 15)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadSet('selectPagePopup', false)
            ->assertPayloadSet('selectAll', false);
    }

    /** @test */
    public function can_unselect_all_rows_on_second_page()
    {
        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selectPage', true)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadNotSet('selectAll', true)
            ->assertPayloadSet('selectPagePopup', true)
            ->assertPayloadSet('page', 1)
            ->assertCount('selected', 15)
            ->call('gotoPage', 2)
            ->assertPayloadSet('page', 2)
            ->set('selectPage', false)
            ->assertPayloadSet('selectPage', false)
            ->assertPayloadSet('selectAll', false)
            ->assertPayloadSet('selectPagePopup', false)
            ->assertCount('selected', 0);
    }

    /** @test */
    public function page_popup_disappears_if_all_rows_are_selected()
    {
        $reduced = $selected = TokenCacheProvider::pluck('id')->toArray();
        Arr::forget($reduced, 0);

        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selected', $reduced)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadSet('selectPagePopup', true)
            ->set('selected', $selected)
            ->assertPayloadSet('selectPage', true)
            ->assertPayloadSet('selectPagePopup', false);
    }

    /** @test */
    public function can_delete_two_selected_clients()
    {
        $selected = TokenCacheProvider::take(2)->pluck('id')->toArray();

        Log::shouldReceive('info')->once()->withArgs(function ($message) {
            return str_contains($message, 'Destroy Token-Cache Provider') == true;
        });

        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selected', $selected)
            ->call('deleteModal')
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->assertSee('You are about to delete 2')
            ->call('deleteProvider')
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.deleted'), 'type' => 'success']);
        $this->assertDatabaseCount(TokenCacheProvider::class, 14);
    }

    /** @test */
    public function can_delete_selected_page()
    {
        Log::shouldReceive('info')->once()->withArgs(function ($message, $context) {
            return str_contains($message, 'Destroy Token-Cache Provider') == true
                && $context['Trigger'] == auth()->user()->getAuthIdentifier()
                && count($context['Resource']) == 15;
        });

        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->set('selectPage', true)
            ->call('deleteModal')
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->assertSee('You are about to delete 15')
            ->call('deleteProvider', json_encode(session('client')))
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.deleted'), 'type' => 'success']);
        $this->assertDatabaseCount(TokenCacheProvider::class, 1);
    }
}
