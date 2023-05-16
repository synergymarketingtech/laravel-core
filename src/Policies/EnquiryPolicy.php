<?php

namespace Coderstm\Core\Policies;

use Coderstm\Core\Models\Admin;
use Coderstm\Core\Models\Enquiry;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnquiryPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  string  $ability
     * @return void|bool
     */
    public function before(Admin $admin, $ability)
    {
        if ($admin->is_supper_admin) {
            return true;
        }
    }

    /**
     * Determine whether the admin can view any models.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Admin $admin)
    {
        if (is_user()) {
            return true;
        }
        return $admin->can('tickets:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  \Coderstm\Core\Models\Enquiry  $enquiry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Admin $admin, Enquiry $enquiry)
    {
        if (is_user()) {
            return $enquiry->email == currentUser()->email;
        }
        return $admin->can('tickets:view');
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin $admin)
    {
        if (is_user()) {
            return true;
        }
        return $admin->can('tickets:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  \Coderstm\Core\Models\Enquiry  $enquiry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin $admin, Enquiry $enquiry)
    {
        if (is_user()) {
            return $enquiry->email == currentUser()->email;
        }
        return $admin->can('tickets:edit');
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Admin $admin)
    {
        return $admin->can('tickets:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Admin $admin)
    {
        return $admin->can('tickets:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Admin $admin)
    {
        return $admin->can('tickets:forceDelete');
    }
}
