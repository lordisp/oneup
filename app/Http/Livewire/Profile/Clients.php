<?php

namespace App\Http\Livewire\Profile;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Passport\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Livewire\Component;

/**
 * @method event(array|Application|Translator|string|null $__, string $string)
 *
 * @property mixed $clients
 */
class Clients extends Component
{
    use WithBulkActions, WithFilteredColumns, WithPerPagePagination, WithSorting;

    public $clients = [];

    public $search;

    public $showModal = false;

    public $name;

    public $redirect;

    public $confidential = false;

    public $secret;

    public function createClient()
    {
        $data = $this->validate([
            'name' => 'required|max:191',
            'redirect' => 'required|url',
            'confidential' => 'required|bool',
        ]);

        $clients = new ClientRepository();
        $client = $clients->create(
            auth()->user()->getAuthIdentifier(), $data['name'], $data['redirect'],
            null, false, false, ! $data['confidential']
        );
        $this->secret = Passport::$hashesClientSecrets ? ['secret' => $client->plainSecret] + $client->toArray() : $client->makeVisible('secret');
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'create']);
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'secret']);
        $this->reset('name', 'redirect', 'confidential');
    }

    public function deleteModal($clientId = null)
    {
        $clientId = isset($clientId) ? (is_array($clientId) ? $clientId : [$clientId]) : $this->selected;
        $this->clients = Client::whereIn('id', $clientId)->get();
        if (count($this->clients) >= 1) {
            $this->dispatchBrowserEvent('open-modal', ['modal' => 'delete']);
        } else {
            $this->event(__('messages.client_delete_error'), 'error');
        }
    }

    public function deleteClient(Request $request)
    {
        if (Gate::inspect('delete-client', [$request->user()])->allowed()) {
            if (Client::destroy($this->clients->pluck('id'))) {
                $this->event(__('messages.client_deleted'), 'success');
                Log::info('Destroy client', [
                    'Trigger' => $request->user()->getAuthIdentifier(),
                    'Resource' => $this->clients->toArray(),
                ]);
            } else {
                $this->event(__('messages.delete_error', ['attribute' => 'Client']), 'error');
            }
        } else {
            $this->event(__('auth.unauthorized', ['value' => 'to delete clients!']), 'error');
        }
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
        $this->resetBulk();
        $this->resetPage();
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
        return $query->when($this->search, fn ($query, $search) => $query
            ->where('name', 'like', '%'.Str::of($search)->trim().'%')
            ->orWhere('id', 'like', '%'.Str::of($search)->trim().'%')
            ->orWhere('redirect', 'like', '%'.Str::of($search)->trim().'%')
        );
    }

    public function getQueryRowsProperty()
    {
        $query = $this->withQuery($this->rows);

        $query = $this->applySorting($query);

        return $this->applyPagination($query);
    }

    public function getRowsProperty(Request $request)
    {
        return $request->user()->clients()->newQuery();
    }

    public function render(): View
    {
        return view('livewire.profile.clients', [
            'rows' => $this->queryRows,
        ]);
    }
}
