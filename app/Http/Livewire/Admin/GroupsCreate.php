<?php

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Http\Livewire\DataTable\WithSearch;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use App\Policies\Policy;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class GroupsCreate extends Component
{
    use WithRbacCache, WithSearch;

    public $group;

    public $results;

    public array $selected = [];

    public array $assignedRoles = [];

    public array $owner = [];

    public array $member = [];

    public array $roles = [];

    public bool $memberAssigment = false;

    public bool $roleAssigment = false;

    public string $mode = '';

    public function mount()
    {
        Policy::gateDenies('group-create');
        $this->group = new Group();
        $this->owner[] = auth()->user()->toArray();
    }

    protected function rules(): array
    {
        return [
            'group.name' => 'required|string|min:5|unique:groups,name',
            'group.description' => 'required|string|min:5',
        ];
    }

    public function save()
    {
        $this->validate();
        $this->group->save();
        $this->group->owners()->attach(Arr::pluck($this->owner, 'id'));

        if (! empty($this->member)) {
            $this->group->attachUsers(Arr::pluck($this->member, 'id'));
        }

        if (! empty($this->roles)) {
            $this->group->attachRoles(Arr::pluck($this->roles, 'id'));
        }

        $this->event('Saved', 'success');
        $this->flushRbacCache();

        return redirect()->to(route('admin.group'));
    }

    public function memberAssigment()
    {
        $this->memberAssigment = ! $this->memberAssigment;

        if (! $this->memberAssigment) {
            $this->search = '';
            $this->selected = [];
        }
    }

    public function roleAssigment($action = null)
    {
        if ($action === 'attach') {
            $this->assignedRoles = $this->roles;
            $this->resetPage();
        } else {
            $this->roleAssigment = ! $this->roleAssigment;
            if (! $this->roleAssigment) {
                $this->search = '';
                $this->selected = [];
            }
        }
    }

    public function updatedSearch($search = null)
    {
        if (! empty($this->search)) {
            $this->mode === 'roles'
                ? $this->results = $this->OemRoles
                : $this->results = $this->OemUsers;
        } else {
            $this->results = [];
        }
    }

    public function getOemUsersProperty(): object
    {
        $email = Str::snake($this->search, '.');

        return User::search('displayName', $this->search)
            ->search('email', $email)
            ->search('firstname', $this->search)
            ->search('lastName', $this->search)
            ->get();
    }

    public function getOemRolesProperty(): object
    {
        return Role::search('name', $this->search)
            ->search('description', $this->search)
            ->get();
    }

    public function add($selected, $type)
    {
        if ($type === 'member') {
            //Add member to Selected-list
            $this->member[] = $selected;
            $this->member = array_map('unserialize', array_unique(array_map('serialize', $this->member)));
        } elseif ($type === 'owner') {
            //Add owner to Selected-list
            $this->owner[] = $selected;
            $this->owner = array_map('unserialize', array_unique(array_map('serialize', $this->owner)));
        } elseif ($type === 'role') {
            //Add role to Selected-list
            $this->roles[] = $selected;
            $this->roles = array_map('unserialize', array_unique(array_map('serialize', $this->roles)));
        }
    }

    public function remove($selected, $type)
    {
        if ($type === 'member') {
            $selected = collect($selected);
            $key = array_search($selected['id'], array_column($this->member, 'id'));
            array_splice($this->member, $key, 1);
        }

        if ($type === 'owner') {
            $selected = collect($selected);
            $key = array_search($selected['id'], array_column($this->owner, 'id'));
            array_splice($this->owner, $key, 1);
        }

        if ($type === 'role') {
            $selected = collect($selected);
            $key = array_search($selected['id'], array_column($this->roles, 'id'));
            array_splice($this->roles, $key, 1);
        }
    }

    public function resetPage()
    {
        $this->search = '';
        $this->results = null;
    }

    public function mode($mode)
    {
        $this->mode = $mode;
    }

    public function render()
    {
        return view('livewire.admin.groups-create');
    }
}
