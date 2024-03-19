<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSearch;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property mixed $rows
 * @property mixed $queryRows
 */
class Groups extends Component
{
    use WithBulkActions, WithFilteredColumns, WithPerPagePagination, WithSearch, WithSorting;

    public Group $group;

    public function mount()
    {
        if (! Gate::any(['group-readAll', 'group-read'])) {
            abort(403);
        }
        $this->group = Group::make();
    }

    public function edit($id): Redirector
    {
        return redirect(route('admin.group.edit', $id));
    }

    public function deleteModal($id = null)
    {
        $id = isset($id) ? (is_array($id) ? $id : [$id]) : $this->selected;
        $this->objects = Group::whereIn('id', $id)->get();
        if (count($this->objects) >= 1) {
            $this->dispatchBrowserEvent('open-modal', ['modal' => 'delete']);
        } else {
            $this->event(__('messages.delete_error', ['attribute' => 'Group']), 'error');
        }
    }

    public function delete(Request $request)
    {
        if (Gate::denies('group-delete', $this->objects)) {
            $this->event(__('auth.unauthorized', ['value' => 'to delete groups!']), 'error');
            $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);

            return redirect()->back();
        }
        if (Group::destroy($this->objects->pluck('id'))) {
            $this->event(__('messages.deleted'), 'success');
            Log::info('Destroy Group', [
                'Trigger' => $request->user()->getAuthIdentifier(),
                'Resource' => $this->objects->toArray(),
            ]);
        } else {
            $this->event(__('messages.delete_error', ['attribute' => 'Group']), 'error');
        }

        $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
        $this->resetBulk();
        $this->resetPage();
    }

    public function withQuery($query)
    {
        return $query->when($this->search, fn ($query, $search) => $query
            ->where('name', 'like', '%'.Str::of($search)->trim().'%')
            ->orWhere('description', 'like', '%'.Str::of($search)->trim().'%')
        );
    }

    public function getQueryRowsProperty()
    {
        $query = $this->withQuery($this->rows);

        $query = $this->applySorting($query);

        return $this->applyPagination($query);
    }

    public function getRowsProperty(Group $group): Builder
    {
        if (Gate::denies('group-readAll')) {
            return auth()->user()->groups('owner')->getQuery();
        } else {
            return $group->newQuery();
        }
    }

    public function render()
    {
        return view('livewire.admin.groups', [
            'rows' => $this->queryRows,
        ]);
    }
}
