<?php

namespace App\Policies\Admin;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->operations()->contains('admin/rbac/group/readAll');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Group $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
    {
        $owners = [];
        foreach (Group::all() as $item) {
            $owners[] = $item->owners()->pluck('id')->flatten()->toArray();
        }
        return $user->operations()->contains('admin/rbac/group/read')
            || in_array($user->id, Arr::flatten($owners));
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->operations()->contains('admin/rbac/group/create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Group $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Group $group)
    {
        return $user->operations()->contains('admin/rbac/group/update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Group $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user/*, Collection|Group|null $group*/)
    {
        return $user->operations()->contains('admin/rbac/group/delete');
    }

    public function detachMembers(User $user, Group $group): bool
    {
        return in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray())
            || $user->operations()->contains('admin/rbac/group/detachMembers');
    }

    public function attachMembers(User $user, Group $group): bool
    {
        return in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray())
            || $user->operations()->contains('admin/rbac/group/attachMembers');
    }

    public function attachRoles(User $user): bool
    {
        return $user->operations()->contains('admin/rbac/group/attachRoles');
    }

    public function detachRoles(User $user): bool
    {
        return $user->operations()->contains('admin/rbac/group/detachRoles');
    }

    public function attachOwners(User $user, Group $group): bool
    {
        return $user->operations()->contains('admin/rbac/group/attachOwners')
            || in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray());
    }

    public function detachOwners(User $user, Group $group): bool
    {
        return $user->operations()->contains('admin/rbac/group/detachOwners')
            || in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray());
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Group $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Group $group)
    {
        return $user->operations()->contains('admin/rbac/group/restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Group $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Group $group)
    {
        return $user->operations()->contains('admin/rbac/group/forceDelete');
    }
}
