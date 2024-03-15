<?php

namespace App\Policies\PCI;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Models\ServiceNowRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceNowRequestPolicy
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
            $this->updateOrCreate('serviceNow/firewall/request/readAll', 'Can read all firewall-requests')
        );
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
    {
        $request = ServiceNowRequest::where('requestor_mail', $user->email)
            ->select('requestor_mail')
            ->first();
        return !empty($request);
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
            $this->updateOrCreate('serviceNow/firewall/import', 'Can import firewall-requests from Service-Now')
        );
    }

  public function invite(User $user): bool
  {
      return $user->operations()->contains(
          $this->updateOrCreate('serviceNow/firewall/invite', 'Can invite firewall-reviewers')
      );
  }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceNowRequest $serviceNowRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ServiceNowRequest $serviceNowRequest)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceNowRequest $serviceNowRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteAll(User $user)
    {
        return $user->operations()->contains(
            $this->updateOrCreate('serviceNow/firewall/request/deleteAll', 'Can delete all firewall-requests')
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceNowRequest $serviceNowRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ServiceNowRequest $serviceNowRequest)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceNowRequest $serviceNowRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ServiceNowRequest $serviceNowRequest)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceNowRequest $serviceNowRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ServiceNowRequest $serviceNowRequest)
    {
        //
    }
}
