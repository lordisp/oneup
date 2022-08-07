<?php

namespace App\Http\Livewire\Rbac;

use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class Users extends component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns;

    public $search;

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
            ->where('firstName', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('lastName', 'like', '%' . Str::of($search)->trim() . '%')
            ->orWhere('email', 'like', '%' . Str::of($search)->trim() . '%')
        );
    }

    public function getUsersProperty()
    {
        $query = User::query();
        $query = $this->withQuery($query);
        $query = $this->applySorting($query);
        return $this->applyPagination($query);
    }

    public function render()
    {
        return view('livewire.rbac.users', [
            'users' => $this->users,
        ]);
    }
}
