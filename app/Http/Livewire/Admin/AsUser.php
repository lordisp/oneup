<?php

namespace App\Http\Livewire\Admin;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AsUser extends Component
{
    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->emit('refresh');
    }

    public function closeSession()
    {
        $userId = session('fromUser');

        $fromUser = User::find($userId);

        if (isset($fromUser)) {
            Log::info($fromUser->email.' closed session from '.auth()->user()->email);
            auth()->logout();
            auth()->login($fromUser);
        }
        session()->flash('fromUser');

        $this->redirect(RouteServiceProvider::HOME);

        $this->emit('refresh');
    }

    public function render(): string
    {
        return <<<'blade'
            <div class="flex w-full p-2 " >
                <div class="flex justify-center items-center w-full rounded-md border border-red-900 bg-red-100 text-red-900">
                    <div>You're logged in as <span> {{ auth()->user()->firstName}} {{ auth()->user()->lastName}} <span class="italic text-sm">({{ auth()->user()->email}})</span> </span>. <x-btn.link wire:click="closeSession">Close Session.</x-btn.link></div>
                </div>
            </div>
blade;
    }
}
