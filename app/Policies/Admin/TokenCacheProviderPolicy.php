<?php

namespace App\Policies\Admin;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\TokenCacheProvider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TokenCacheProviderPolicy
{
    use HandlesAuthorization, WithRbacCache;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/readAll', 'Can read all Providers')
        );
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\TokenCacheProvider  $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ?TokenCacheProvider $tokenCacheProvider = null): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/read', 'Can read Provider')
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/create', 'Can create Provider')
        );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\TokenCacheProvider  $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/update', 'Can update Provider')
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/delete', 'Can delete Provider')
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ?TokenCacheProvider $tokenCacheProvider): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/restore', 'Can restore Provider')
        );

    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ?TokenCacheProvider $tokenCacheProvider): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/forceDelete', 'Can force delete Provider')
        );
    }
}
