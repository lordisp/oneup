<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithRbacCache;
use App\Http\Livewire\DataTable\WithSearch;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Operation;
use App\Models\Role;
use App\Policies\Policy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property mixed $queryRows
 * @property mixed $rows
 */
class RolesEdit extends Component
{
    public Operation $operation;
    public Role $role;
    public $roleId;

    use WithPerPagePagination, WithSorting, WithFilteredColumns, WithBulkActions, WithSearch, WithRbacCache;

    protected function rules(): array
    {
        return [
            'role.name' => 'required|string|min:5|unique:roles,name,' . $this->role->id,
            'role.description' => 'required|string|min:5',
            'selected' => 'required'
        ];
    }

    protected $messages = [
        'selected' => 'At least one operation is required for a role.',
    ];

    public function mount()
    {
        Policy::gateDenies('roles-update');
        if (empty($this->roleId)) {
            $this->roleId = request()->route('id');
        }
        $this->setRole($this->roleId);
    }

    protected function setRole($id)
    {
        $this->role = $id ? Role::findOrFail($id) : Role::make();
        if ($id) $this->selected = $this->role->operations->pluck('id')->flatten()->toArray();
    }

    public function save()
    {
        $this->validate();
        $this->role->save();
        $this->role->operations()->sync($this->selected);
        $this->role = Role::make();
        $this->event('Saved', 'success');
        $this->flushRbacCache();
        return redirect()->to(route('admin.roles'));
    }

    public function cancel()
    {
        return redirect()->to(route('admin.roles'));
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
        return $query->paginate(10);
    }

    public function getRowsProperty(Operation $operation): Builder
    {
        return $operation->newQuery();
    }

    public function render(): View
    {
        return view('livewire.admin.roles-edit', [
            'rows' => $this->queryRows,
        ]);
    }

}
