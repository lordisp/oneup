<?php

namespace App\Http\Livewire;

use Livewire\Component;

/**
 * @property mixed $unreadNotifications
 * @property mixed $readNotifications
 */
class Notifications extends Component
{
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function read($key)
    {
        $notification = auth()->user()->unreadNotifications->find($key);

        if ($notification) $notification->markAsRead();

        $this->emit('refreshComponent');
    }

    public function getUnreadNotificationsProperty()
    {
        return auth()->user()->unreadNotifications->map(function ($foo) {
            return [
                'id' => $foo->id,
                'message' => data_get($foo->data, 'message'),
                'title' => data_get($foo->data, 'title'),
            ];
        });

    }

    public function render()
    {
        return view('livewire.notifications', [
            'unreadNotifications' => $this->unreadNotifications,
        ]);
    }
}
