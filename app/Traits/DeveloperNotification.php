<?php

namespace App\Traits;

use App\Models\User;

trait DeveloperNotification
{
    protected function sendDeveloperNotification($exception): void
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\DeveloperNotification($exception));
        }
    }

    protected function getUsers()
    {
        return User::whereRelation('groups', 'name', '=', 'Developers')->get();
    }
}
