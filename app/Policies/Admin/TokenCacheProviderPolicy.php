<?php

namespace App\Policies\Admin;

use App\Models\TokenCacheProvider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TokenCacheProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TokenCacheProvider  $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TokenCacheProvider $tokenCacheProvider)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TokenCacheProvider  $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TokenCacheProvider  $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TokenCacheProvider $tokenCacheProvider)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TokenCacheProvider  $tokenCacheProvider
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TokenCacheProvider $tokenCacheProvider)
    {
        //
    }
}
