<?php

namespace App\Policies\Admin;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

class GroupPolicy
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
        return $user->operations()->contains($this->updateOrCreate('admin/rbac/group/readAll', 'Can read all groups',));
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
        $groups = cache()->tags('rbac')->remember('groups', 3600, function () {
            return Group::all();
        });
        foreach ($groups as $item) {
            $owners[] = cache()->tags('rbac')->remember($user->id . $item->id, 3600, fn() => $item->owners()->pluck('id')->flatten()->toArray());
        }
        return $user->operations()->contains($this->updateOrCreate('admin/rbac/group/readAll', 'Can read all groups',))
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
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/create', 'Can create a group')
        );
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
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/update', 'Can update groups')
        );
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
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/delete', 'Can delete groups')
        );
    }

    public function detachMembers(User $user, Group $group): bool
    {
        return in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray())
            || $user->operations()->contains(
                $this->updateOrCreate('admin/rbac/group/detachMembers', 'Can remove members from groups')
            );
    }

    public function attachMembers(User $user, Group $group): bool
    {
        return in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray())
            || $user->operations()->contains(
                $this->updateOrCreate('admin/rbac/group/attachMembers', 'Can add members to groups')
            );
    }

    public function attachRoles(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/attachRoles', 'Can add roles to a group')
        );
    }

    public function detachRoles(User $user): bool
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/detachRoles', 'Can detach roles from a group')
        );


    }

    public function attachOwners(User $user, Group $group): bool
    {
        return $user->operations()->contains(
                $this->updateOrCreate('admin/rbac/group/attachOwners', 'Can add owners to groups')
            )
            || in_array($user->id, $group->owners()->pluck('id')->flatten()->toArray());
    }

    public function detachOwners(User $user, Group $group): bool
    {
        return $user->operations()->contains(
                $this->updateOrCreate('admin/rbac/group/detachOwners', 'Can remove owners from groups')
            )
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
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/restore', 'Can restore groups')
        );
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
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/group/forceDelete', 'Can force-delete groups')
        );
    }
}
