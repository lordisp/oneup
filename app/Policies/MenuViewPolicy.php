<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuViewPolicy
{
    use HandlesAuthorization;

    public function viewMenu(User $user): bool
    {
        return $user->canAny([
            'user-readAll',
            'roles-read',
            'roles-readAll',
            'operation-read',
            'operation-readAll',
            'group-read',
            'group-readAll',
            'provider-read',
            'provider-readAll',
            'serviceNow-firewallRequests-read',
            'serviceNow-firewallRequests-readAll',
        ]);
    }
}
