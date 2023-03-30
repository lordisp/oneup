<?php

namespace App\Http\Livewire\Rbac;

use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Log;

class Users extends component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns;

    public $search;
    public $modalUser;

    public $modalLock = false;
    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        Gate::authorize('user-readAll');
    }

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
        Gate::authorize('user-loginAs');
        $asUser = User::find($userId);

        session()->put('fromUser', auth()->id());

        Log::info(auth()->user()->email . ' logged in as ' . $asUser->email);

        auth()->login($asUser);
        $this->emit('refresh');
        return redirect(RouteServiceProvider::HOME);
    }

    public function openLogoutUserModal($userId)
    {
        if (Gate::denies('user-lock')) abort(403, "You're not authorizes to logout other user's sessions.");
        $this->modalLock = false;
        $this->modalUser = User::find($userId);

        $this->dispatchBrowserEvent('open-modal', ['modal' => 'logout-user']);
    }

    public function closeLogoutUserModal()
    {
        $this->modalLock = false;
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'logout-user']);
        $this->emit('refresh');
    }

    public function logoutUser()
    {
        Gate::authorize('user-lock');
        $user = auth()->user();
        Auth::login($this->modalUser);
        Auth::logoutOtherDevices(md5(config('app.key')));
        auth()->login($user);
        if ($this->modalLock) {
            $this->modalUser->status = 0;
            $this->modalUser->save();
        }
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'logout-user']);
        $this->event("{$this->modalUser->email} has been logged out.", 'success');
        $this->redirect(route('admin.users'));
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
