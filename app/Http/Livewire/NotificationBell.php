<?php

namespace App\Http\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public function getStatusProperty()
    {
        return auth()->user()->unreadNotifications->count() > 0;
    }

    public function render()
    {
        return <<<'blade'
            <div>
                <div wire:poll.60s class="relative inline-block">
                    <x-icon.bell size="6"/>
                    @if($this->status)
                    <span class="animate-pulse absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
                    @endif
                </div>
            </div>
        blade;
    }
}
