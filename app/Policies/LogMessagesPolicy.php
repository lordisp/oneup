<?php

namespace App\Policies;

use App\Models\LogMessage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogMessagesPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, LogMessage $logMessages): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, LogMessage $logMessages): bool
    {
    }

    public function delete(User $user, LogMessage $logMessages): bool
    {
    }

    public function restore(User $user, LogMessage $logMessages): bool
    {
    }

    public function forceDelete(User $user, LogMessage $logMessages): bool
    {
    }
}
