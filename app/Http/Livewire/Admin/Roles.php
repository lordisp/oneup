<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Operation;
use App\Models\Role;
use App\Policies\Policy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property mixed $rows
 * @property mixed $queryRows
 */
class Roles extends Component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns, WithBulkActions;

    public string $search = '';
    public Role $role;

    public function mount()
    {
        Policy::gateDenies('roles-readAll');
        $this->role = Role::make();
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function deleteModal($id = null)
    {
        $id = isset($id) ? (is_array($id) ? $id : [$id]) : $this->selected;
        $this->objects = Role::whereIn('id', $id)->get();
        if (count($this->objects) >= 1) $this->dispatchBrowserEvent('open-modal', ['modal' => 'delete']); else {
            $this->event(__('messages.delete_error', ['attribute' => 'Role']), 'error');
        }
    }

    public function delete(Request $request)
    {
        if (Gate::denies('roles-delete', $this->objects)) {
            $this->event(__('auth.unauthorized', ['value' => 'to delete roles!']), 'error');
            $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
            return redirect()->back();
        }
        if (Role::destroy($this->objects->pluck('id'))) {
            $this->event(__('messages.deleted'), 'success');
            Log::info('Destroy Role', [
                'Trigger' => $request->user()->getAuthIdentifier(),
                'Resource' => $this->objects->toArray(),
            ]);
        } else $this->event(__('messages.delete_error', ['attribute' => 'Role']), 'error');

        $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
        $this->resetBulk();
        $this->resetPage();
    }

    public function edit($id): Redirector
    {
        return redirect(route('admin.roles.edit', $id));
    }

    public function withQuery($query)
    {
        return $query->when($this->search, fn($query, $search) => $query
            ->where('name', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('description', 'like', '%' . Str::of($search)->trim() . '%')
        );
    }

    public function getQueryRowsProperty()
    {
        $query = $this->withQuery($this->rows);

        $query = $this->applySorting($query);

        return $this->applyPagination($query);
    }

    public function getRowsProperty(Role $role): Builder
    {
        return $role->newQuery();
    }

    public function render()
    {
        return view('livewire.admin.roles', [
            'rows' => $this->queryRows,
        ]);
    }
}
