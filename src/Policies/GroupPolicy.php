<?php

namespace Coderstm\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @param  string  $ability
     * @return void|bool
     */
    public function before(Model $admin, $ability)
    {
        if ($admin->is_supper_admin) {
            return true;
        }
    }

    /**
     * Determine whether the admin can view any models.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Model $admin)
    {
        return $admin->can('groups:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Model $admin)
    {
        return $admin->can('groups:view');
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Model $admin)
    {
        return $admin->can('groups:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Model $admin)
    {
        return $admin->can('groups:edit');
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Model $admin)
    {
        return $admin->can('groups:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Model $admin)
    {
        return $admin->can('groups:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Model $admin)
    {
        return $admin->can('groups:forceDelete');
    }
}
