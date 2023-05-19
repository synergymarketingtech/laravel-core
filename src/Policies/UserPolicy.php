<?php

namespace Coderstm\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $admin->can('members:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Model $admin, Model $user)
    {
        if (isUser()) {
            return $user->id == currentUser()->id;
        }
        return $admin->can('members:view');
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Model $admin)
    {
        return $admin->can('members:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Model $admin)
    {
        return $admin->can('members:edit');
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Model $admin)
    {
        return $admin->can('members:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Model $admin)
    {
        return $admin->can('members:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Model $admin)
    {
        return $admin->can('members:forceDelete');
    }
}
