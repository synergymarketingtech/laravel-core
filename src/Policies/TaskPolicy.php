<?php

namespace Coderstm\Core\Policies;

use Coderstm\Core\Models\Admin;
use Coderstm\Core\Models\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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
        return $admin->can('tasks:list');
    }

    /**
     * Determine whether the admin can view the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  \Coderstm\Core\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Admin $admin, Task $task)
    {
        return $admin->can('tasks:view') && ($task->user_id == $admin->id || $task->hasUser($admin->id));
    }

    /**
     * Determine whether the admin can create models.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin $admin)
    {
        return $admin->can('tasks:new');
    }

    /**
     * Determine whether the admin can update the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @param  \Coderstm\Core\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin $admin, Task $task)
    {
        return $admin->can('tasks:edit') && ($task->user_id == $admin->id || $task->hasUser($admin->id));
    }

    /**
     * Determine whether the admin can delete the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Admin $admin)
    {
        return $admin->can('tasks:delete');
    }

    /**
     * Determine whether the admin can restore the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Admin $admin)
    {
        return $admin->can('tasks:restore');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     *
     * @param  \Coderstm\Core\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Admin $admin)
    {
        return $admin->can('tasks:forceDelete');
    }
}
