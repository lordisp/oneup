<?php

namespace App\Policies;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PmaPolicy
{
    use HandlesAuthorization, WithRbacCache;

    public function view(User $user): bool
    {
        return $this->hasPmaReadPermission($user) && $this->isNotProduction();
    }

    protected function hasPmaReadPermission(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('developer/pma/read', 'Allows users to access and manage PhpMyAdmin\'s database management features within the staging environment')
        );
    }

    protected function isNotProduction(): bool
    {
        return config('app.env') !== 'production';
    }
}
