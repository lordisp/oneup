<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithRbacCache;
use App\Http\Livewire\DataTable\WithSearch;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * @property mixed $group
 * @property mixed $queryRows
 * @property mixed $rows
 */
class GroupsEdit extends Component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns, WithBulkActions, WithSearch, WithRbacCache;

    // WithEditPage
    public Model $edit;
    public string $rowId;


    public string $tab;
    public $searchMember, $checkbox;
    public int $paginate = 5;
    public bool $active = false;

    public array|Collection $results;
    public array $selectedResults = [];

    public function mount()
    {
        if (empty($this->rowId)) $this->rowId = request()->route('id');

        $this->setEdit(Group::make(), $this->rowId);

        if (!Gate::any(['group-update', 'group-read'], $this->edit)) abort(403);

        if (Gate::denies('group-detach-members', $this->edit)) $this->active = false;

        if (empty($this->tab)) $this->tab = request()->route('tab');
    }

    public function updatedSelected()
    {
        $this->selectPage = count($this->selected) >= $this->queryRows->count();

        $this->selectPagePopup = count($this->selected) >= $this->queryRows->total()
            ? false
            : $this->selectPage;

        $this->active = Gate::any(['group-attach-members', 'group-detach-members'], $this->edit) && !empty($this->selected);
    }

    //WithEditPage
    protected function setEdit($model, $id)
    {
        $this->edit = $id ? $model::findOrFail($id) : $model::make();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function notAllowed(): void
    {
        $this->clearSideOver();
        $this->event('Operation not allowed!', 'error');
    }

    /* Computed Properties */
    public function updatedSearch(): void
    {
        if (empty($this->search)) {
            $this->results = [];
        }

        // get all filtered results from the model
        $selected = Arr::flatten(data_get($this->selectedResults, '*.id'));

        $this->results = match ($this->tab) {
            'members' => User::where(function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%')
                    ->orwhere('displayName', 'like', '%' . $this->search . '%');
            })
                ->where(function ($query) use ($selected) {
                    $query->whereNotIn('id', $this->edit->users()->pluck('id')->toArray());
                    if (isset($selected)) $query->whereNotIn('id', $selected);
                })
                ->select(['id', 'email', 'displayName', 'firstName', 'lastName', 'avatar'])
                ->get(),
            'owners' => User::where(function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%')
                    ->orwhere('displayName', 'like', '%' . $this->search . '%');
            })
                ->where(function ($query) use ($selected) {
                    $query->whereNotIn('id', $this->edit->owners()->pluck('id')->toArray());
                    if (isset($selected)) $query->whereNotIn('id', $selected);
                })
                ->select(['id', 'email', 'displayName', 'firstName', 'lastName', 'avatar'])
                ->get(),

            'roles' => Role::where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orwhere('description', 'like', '%' . $this->search . '%');
            })
                ->where(function ($query) use ($selected) {
                    $query->whereNotIn('id', $this->edit->roles()->pluck('id')->toArray());
                    if (isset($selected)) $query->whereNotIn('id', $selected);
                })
                ->select(['id', 'name', 'description'])
                ->get(),
            default => collect([]),
        };

    }

    public function add($selected)
    {
        $this->selectedResults[] = $selected;
        $this->updatedSearch();
    }

    public function remove($selected)
    {
        $key = array_search($selected['id'], array_column($this->selectedResults, 'id'));
        array_splice($this->selectedResults, $key, 1);
        $this->updatedSearch();
    }

    public function save()
    {
        $ids = array_unique(data_get($this->selectedResults, '*.id'));

        switch ($this->tab) {
            case 'members':
                if (Gate::allows('group-attach-members', $this->edit)) {
                    $this->edit->attachUsers(array_unique($ids));
                    $this->clearSideOver();
                    $this->event(Str::ucfirst(count(array_unique($ids)) > 1 ? Str::plural($this->tab) . ' have ' : Str::singular($this->tab) . ' has ') . 'been assigned!', 'success');
                } else {
                    $this->notAllowed();
                }
                break;
            case 'roles':
                if (Gate::allows('group-attach-roles')) {
                    $this->edit->attachRoles(array_unique($ids));
                    $this->clearSideOver();
                    $this->event(Str::ucfirst(count(array_unique($ids)) > 1 ? Str::plural($this->tab) . ' have ' : Str::singular($this->tab) . ' has ') . 'been assigned!', 'success');
                } else {
                    $this->notAllowed();
                }
                break;
            case 'owners':
                if (Gate::allows('group-attach-owners', $this->edit)) {
                    $this->edit->attachOwners(array_unique($ids));
                    $this->clearSideOver();
                    $this->event(Str::ucfirst(count(array_unique($ids)) > 1 ? Str::plural($this->tab) . ' have ' : Str::singular($this->tab) . ' has ') . 'been assigned!', 'success');
                } else {
                    $this->notAllowed();
                }
                break;
        }
        $this->flushRbacCache();
    }

    public function clearSideOver()
    {
        $this->results = [];
        $this->reset(['selectPage', 'active', 'search', 'selectedResults', 'selected']);
    }

    public function detachModal()
    {
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'delete']);
    }

    public function detach()
    {
        if (Gate::denies('group-detach-members', [$this->edit])) abort(403);
        switch ($this->tab) {
            case 'members':
                $this->edit->detachUsers($this->selected);
                break;
            case 'owners':
                if ($this->edit->owners()->count() - count($this->selected) < 1) {
                    $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
                    $this->event('There must be at least one owner assigned!', 'error');
                    break;
                }
                $this->edit->detachOwners($this->selected);
                break;
            case
            'roles':
                $this->edit->unassignRole($this->selected);
                break;
        }
        if (!$this->dispatchQueue) {
            $this->dispatchBrowserEvent('close-modal', ['modal' => 'delete']);
            $this->event('Successfully removed selected ' . Str::ucfirst($this->tab) . '!', 'success');
            $this->flushRbacCache();
        }
        $this->clearSideOver();
    }

    public
    function withQuery($query)
    {
        return $this->tab == 'roles' ? $query->when($this->search, fn($query, $search) => $query
            ->where('name', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('description', 'like', '%' . Str::of($search)->trim() . '%'))
            : $query->when($this->search, fn($query, $search) => $query
                ->where('displayName', 'like', '%' . Str::of($search)->trim() . '%')
                ->orWhere('email', 'like', '%' . Str::of($search)->trim() . '%'));
    }

    public
    function getQueryRowsProperty()
    {
        $query = $this->withQuery($this->rows);

        $query = $this->applySorting($query);

        return ($query instanceof Collection) ? $query : $this->applyPagination($query);
    }


    public function getRowsProperty(): Collection|BelongsToMany
    {
        switch ($this->tab) {
            case 'members':
                $rows = $this->edit->users()->newQuery();
                break;
            case 'owners':
                $rows = $this->edit->owners()->newQuery();
                break;
            case 'roles':
                $rows = $this->edit->roles()->newQuery();
        }

        return (isset($rows)) ? $rows : collect([]);
    }

    public function render()
    {
        return view('livewire.admin.groups-edit', [
            'rows' => $this->queryRows
        ]);
    }
}
