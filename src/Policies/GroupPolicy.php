<?php

namespace CoderstmCore\Policies;

use CoderstmCore\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
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
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Admin $admin)
    {
        return $admin->can('groups:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Admin $admin)
    {
        return $admin->can('groups:view');
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin $admin)
    {
        return $admin->can('groups:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin $admin)
    {
        return $admin->can('groups:edit');
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Admin $admin)
    {
        return $admin->can('groups:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Admin $admin)
    {
        return $admin->can('groups:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \CoderstmCore\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Admin $admin)
    {
        return $admin->can('groups:forceDelete');
    }
}
