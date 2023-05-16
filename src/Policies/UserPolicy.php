<?php

namespace Coderstm\Core\Policies;

use Coderstm\Core\Models\User;
use Coderstm\Core\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $admin->can('members:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  \Coderstm\Core\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Admin $admin, User $user)
    {
        if (is_user()) {
            return $user->id == currentUser()->id;
        }
        return $admin->can('members:view');
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin $admin)
    {
        return $admin->can('members:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin $admin)
    {
        return $admin->can('members:edit');
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Admin $admin)
    {
        return $admin->can('members:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Admin $admin)
    {
        return $admin->can('members:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Admin $admin)
    {
        return $admin->can('members:forceDelete');
    }

    /**
     * Determine whether the admin can view monthly reports of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reports_monthly(Admin $admin)
    {
        return $admin->can('reports:monthly-reports');
    }

    /**
     * Determine whether the admin can view yearly reports of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reports_yearly(Admin $admin)
    {
        return $admin->can('reports:yearly-reports');
    }
    /**
     * Determine whether the admin can update parq of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  \Coderstm\Core\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update_parq(Admin $admin, User $user)
    {
        if (is_user()) {
            return $user->id == currentUser()->id;
        }
        return $admin->can('members:enquiry');
    }

    /**
     * Determine whether the admin can view enquiry of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function enquiry(Admin $admin)
    {
        return $admin->can('members:enquiry');
    }

    /**
     * Determine whether the admin can update admin of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function admin(Admin $admin)
    {
        return $admin->can('finance:admin');
    }

    /**
     * Determine whether the admin can update membership of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function membership(Admin $admin)
    {
        return $admin->can('finance:membership');
    }

    /**
     * Determine whether the admin can update types of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function types(Admin $admin)
    {
        return $admin->can('finance:type');
    }

    /**
     * Determine whether the admin can use reconciles of the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reconciles(Admin $admin)
    {
        return $admin->can('finance:reconcile') || $admin->can('reconcile:rolling') || $admin->can('reconcile:yearly') || $admin->can('reconcile:end-date');
    }
}
