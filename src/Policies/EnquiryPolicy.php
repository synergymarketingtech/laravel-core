<?php

namespace Coderstm\Policies;

use App\Models\Admin;
use App\Models\User;
use Coderstm\Models\Enquiry;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnquiryPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\Admin|User  $admin
     * @param  string  $ability
     * @return void|bool
     */
    public function before(Admin|User $admin, $ability)
    {
        if ($admin->is_supper_admin) {
            return true;
        }
    }

    /**
     * Determine whether the admin can view any models.
     *
     * @param  \App\Models\Admin|User  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Admin|User $admin)
    {
        if (isUser()) {
            return true;
        }
        return $admin->can('tickets:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \App\Models\Admin|User  $admin
     * @param  \App\Models\Core\Enquiry  $enquiry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Admin|User $admin, Enquiry $enquiry)
    {
        if (isUser()) {
            return $enquiry->email == currentUser()->email;
        }
        return $admin->can('tickets:view');
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \App\Models\Admin|User  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin|User $admin)
    {
        if (isUser()) {
            return true;
        }
        return $admin->can('tickets:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \App\Models\Admin|User  $admin
     * @param  \App\Models\Core\Enquiry  $enquiry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin|User $admin, Enquiry $enquiry)
    {
        if (isUser()) {
            return $enquiry->email == currentUser()->email;
        }
        return $admin->can('tickets:edit');
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Admin $admin)
    {
        return $admin->can('tickets:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Admin $admin)
    {
        return $admin->can('tickets:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Admin $admin)
    {
        return $admin->can('tickets:forceDelete');
    }
}
