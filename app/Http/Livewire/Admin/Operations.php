<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Operation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property mixed $rows
 * @property mixed $queryRows
 */
class Operations extends Component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns, WithBulkActions;

    public string $search = '';
    public Operation $operation;

    protected function rules(): array
    {
        return [
            'operation.operation' => 'required|min:10|unique:operations,operation,'.$this->operation->id,
            'operation.description' => 'required|string|min:5',
        ];
    }

    public function mount()
    {
        $this->operation = Operation::make();
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function openCreateModal()
    {
        $this->operation = Operation::make();
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'create']);
    }

    public function closeModal()
    {
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'edit']);
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'create']);
        $this->operation = Operation::make();
        $this->reset('search');
        $this->resetErrorBag();
    }

    public function editModal(Operation $operation)
    {
        $this->operation = $operation;
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'edit']);
    }
    public function save()
    {
        $this->validate();
        $this->operation->save();
        $this->closeModal();
        $this->event('Saved', 'success');
    }

    public function deleteModal($id = null)
    {
        $id = isset($id) ? (is_array($id) ? $id : [$id]) : $this->selected;
        $this->objects = Operation::whereIn('id', $id)->get();
        if (count($this->objects) >= 1) {
            $this->dispatchBrowserEvent('open-modal', ['modal' => 'delete']);
        } else {
            $this->event(__('messages.delete_error', ['attribute' => 'Operation']), 'error');
        }
    }

    public function deleteOperation(Request $request)
    {
        if (Gate::inspect('delete-provider', [$request->user()])->allowed()) {
            if (Operation::destroy($this->objects->pluck('id'))) {
                $this->event(__('messages.deleted'), 'success');
                Log::info('Destroy Operation', [
                    'Trigger' => $request->user()->getAuthIdentifier(),
                    'Resource' => $this->objects->toArray(),
                ]);
            } else {
                $this->event(__('messages.delete_error', ['attribute' => 'Operation']), 'error');
            }
        } else {
            $this->event(__('auth.unauthorized', ['value' => 'to delete operation!']), 'error');
        }
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
        $this->resetBulk();
        $this->resetPage();
    }

    public function withQuery($query)
    {
        return $query->when($this->search, fn($query, $search) => $query
            ->where('operation', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('description', 'like', '%' . Str::of($search)->trim() . '%')
        );
    }

    public function getQueryRowsProperty()
    {
        $query = $this->withQuery($this->rows);

        $query = $this->applySorting($query);

        return $this->applyPagination($query);
    }

    public function getRowsProperty(Operation $operation): Builder
    {
        return $operation->newQuery();
    }

    public function render(): View
    {
        return view('livewire.admin.operations', [
            'rows' => $this->queryRows,
        ]);
    }
}
