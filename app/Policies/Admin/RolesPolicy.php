<?php

namespace App\Policies\Admin;

use App\Models\Operation;
use App\Models\Role;
use App\Models\User;
use App\Policies\Policy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Collection;

class RolesPolicy extends Policy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->operations()->contains(
            cache()->tags('rbac')->remember('admin/rbac/role/readAll', 3600, function () {
                return Operation::updateOrCreate(
                    ['operation' => 'admin/rbac/role/readAll'],
                    ['description' => 'Can attach roles to a group']
                )->operation;
            })
        );
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
    {
        return $user->operations()->contains('admin/rbac/role/read');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->operations()->contains('admin/rbac/role/create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user)
    {
        return $user->operations()->contains('admin/rbac/role/update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Collection|Role|null $role)
    {
        $Name = 'Global Administrator';
        if ($role instanceof Collection) {
            foreach ($role as $value) {
                if ($value->name == $Name) {
                    return false;
                }
            }
        } else {
            return ! ($role->name == $Name);
        }

        return $user->operations()->contains('admin/rbac/role/delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Role $role)
    {
        return $user->operations()->contains('admin/rbac/role/restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user)
    {
        return $user->operations()->contains('admin/rbac/role/forceDelete');
    }
}
