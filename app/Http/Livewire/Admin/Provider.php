<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\TokenCacheProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property mixed $rows
 * @property mixed $queryRows
 */
class Provider extends Component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns, WithBulkActions;

    public array $client = [];
    public string $search = '', $type = '';
    public TokenCacheProvider $provider;

    protected $rules = [
        'provider.name' => 'required|min:5|max:125',
        'provider.auth_url' => 'required|string|min:5|max:125',
        'provider.token_url' => 'required|string|min:5|max:125',
        'provider.auth_endpoint' => 'required|active_url',
        'client.tenant' => 'required|uuid',
        'client.client_id' => 'required|uuid',
        'client.client_secret' => 'required|string|min:16',
        'client.resource' => 'required_without:client.scope|active_url',
        'client.scope' => 'required_without:client.resource|min:3',
        'type' => 'required'
    ];

    public function mount(){
        $this->provider=TokenCacheProvider::make();
    }

    public function openCreateModal()
    {
        $this->provider = TokenCacheProvider::make();
        $this->reset('client','type');
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'create']);
    }

    public function editModal(TokenCacheProvider $provider)
    {
        $this->provider = $provider;
        $this->client = json_decode($provider->client, true);
        $this->type = Arr::has($this->client, 'resource') ? 'arm' : 'graph';
        $this->client['client_secret'] = decrypt($this->client['client_secret']);
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'edit']);
    }

    public function save()
    {
        $this->validate();

        $this->client['client_secret'] = encrypt($this->client['client_secret']);

        $this->provider->client = json_encode($this->client);

        $this->provider->save();

        $this->closeModal();

        $this->event('Saved', 'success');
    }


    public function deleteModal($id = null)
    {
        $id = isset($id) ? (is_array($id) ? $id : [$id]) : $this->selected;
        $this->objects = TokenCacheProvider::whereIn('id', $id)->get();
        if (count($this->objects) >= 1) {
            $this->dispatchBrowserEvent('open-modal', ['modal' => 'delete']);
        } else {
            $this->event(__('messages.delete_error', ['attribute' => 'Provider']), 'error');
        }
    }


    public function deleteProvider(Request $request)
    {
        if (Gate::inspect('provider-delete', [$request->user()])->allowed()) {
            if (TokenCacheProvider::destroy($this->objects->pluck('id'))) {
                $this->event(__('messages.deleted'), 'success');
                Log::info('Destroy Token-Cache Provider', [
                    'Trigger' => $request->user()->getAuthIdentifier(),
                    'Resource' => $this->objects->toArray(),
                ]);
            } else {
                $this->event(__('messages.delete_error', ['attribute' => 'Provider']), 'error');
            }
        } else {
            $this->event(__('auth.unauthorized', ['value' => 'to delete provider!']), 'error');
        }
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
        $this->resetBulk();
        $this->resetPage();
    }


    public function closeModal()
    {
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'edit']);
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'create']);
        $this->provider = TokenCacheProvider::make();
        $this->reset('client', 'type', 'search');
        $this->resetErrorBag();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function withQuery($query)
    {
        return $query->when($this->search, fn($query, $search) => $query
            ->where('name', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('id', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('auth_url', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('token_url', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('auth_endpoint', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('client', 'like', '%' . Str::of($search)->trim() . '%')
        );
    }

    public function getQueryRowsProperty()
    {
        $query = $this->withQuery($this->rows);

        $query = $this->applySorting($query);

        return $this->applyPagination($query);
    }

    public function getRowsProperty(TokenCacheProvider $provider): Builder
    {
        return $provider->withCasts(['clients' => 'json'])->newQuery();
    }

    public function render(): View
    {
        return view('livewire.admin.provider', [
            'rows' => $this->queryRows,
        ]);
    }
}
