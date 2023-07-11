<?php

namespace App\Policies\Admin;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\Operation;
use App\Models\User;
use App\Policies\Policy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class OperationPolicy extends Policy
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
            $this->updateOrCreate('admin/rbac/operation/readAll', 'Can read all operations')
        );
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Operation $operation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Operation|null $operation = null)
    {
        return $user->operations()->contains('admin/rbac/operation/read');
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
            $this->updateOrCreate('admin/rbac/operation/create', 'Can create operations')
        );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Operation $operation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Operation|null $operation)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/operation/update', 'Can update operations')
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Operation $operation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Operation|Collection|null $operation)
    {
        if (isset($operation) && $operation instanceof Collection) {
            foreach ($operation as $value) {
                return !Arr::has(self::operations, $value->operation);
            }
        }
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/operation/delete', 'Can delete operations')
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Operation $operation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Operation|null $operation)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/operation/restore', 'Can restore operations')
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Operation $operation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Operation|null $operation)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('admin/rbac/operation/forceDelete', 'Can force-delete operations')
        );
    }
}
