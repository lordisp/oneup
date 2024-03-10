<?php

namespace App\Policies;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MailhogPolicy
{
    use HandlesAuthorization, WithRbacCache;

    public function view(User $user): bool
    {
        return $this->hasMailhogReadPermission($user) && $this->isStagingEnvironment();
    }

    protected function hasMailhogReadPermission(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('developer/mailhog/read', 'Allows users to access and manage Mailhog\'s email testing and debugging features within the staging environment')
        );
    }

    protected function isStagingEnvironment(): bool
    {
        return config('app.env') === 'stage';
    }
}
