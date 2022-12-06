<?php

namespace App\Http\Livewire\Rbac;

use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Str;
use Livewire\Component;
use Log;

class Users extends component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns;

    public $search;

    protected $listeners = ['refresh' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function loginAs($userId)
    {
        $asUser = User::find($userId);

        session()->put('fromUser', auth()->id());

        Log::info(auth()->user()->email . ' logged in as ' . $asUser->email);

        auth()->logout();
        auth()->login($asUser);

        $this->redirect(RouteServiceProvider::HOME);
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
