<?php

namespace App\Policies\Admin;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
            $this->updateOrCreate('admin/rbac/user/readAll', 'Can read all users')
        );
    }

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
        ]);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @return bool
     */
    public function loginAs(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/user/loginAs', 'Can login as a given user')
        );
    }

    public function lockUser(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/user/lockUser', 'Can lock a given user and end all open sessions.')

        );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/user/update', 'Can update users profiles')
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/user/delete', 'Can delete users.')
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }
}
