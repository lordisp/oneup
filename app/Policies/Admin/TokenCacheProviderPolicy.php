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
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/readAll', 'Can read all Providers')
        );
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\TokenCacheProvider $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TokenCacheProvider|null $tokenCacheProvider = null)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/read', 'Can read Provider')
        );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/create', 'Can create Provider')
        );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\TokenCacheProvider $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/update', 'Can update Provider')
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/delete', 'Can delete Provider')
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\TokenCacheProvider $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TokenCacheProvider|null $tokenCacheProvider)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/restore', 'Can restore Provider')
        );

    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\TokenCacheProvider $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TokenCacheProvider|null $tokenCacheProvider)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/tokenCacheProvider/forceDelete', 'Can force delete Provider')
        );
    }
}
