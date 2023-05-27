<?php

namespace Coderstm\Models;

use Coderstm\Coderstm;
use Coderstm\Traits\Core;
use Coderstm\Enum\AppStatus;
use Coderstm\Traits\Fileable;
use Coderstm\Traits\TaskUser;
use Coderstm\Events\TaskCreated;
use Coderstm\Models\Task\Reply;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use Core, Fileable, TaskUser;

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TaskCreated::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'message',
        'status',
        'user_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_archived'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'last_reply.user',
        'user',
        'users',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => AppStatus::class,
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * Get all of the replies for the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class, 'task_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the task's most recent reply.
     */
    public function last_reply()
    {
        return $this->hasOne(Reply::class, 'task_id')->latestOfMany();
    }

    /**
     * The archives that belong to the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function archives(): BelongsToMany
    {
        return $this->belongsToMany(Coderstm::$adminModel, 'task_archives', 'task_id', 'user_id');
    }

    /**
     * Get the is archived for current user
     *
     * @return bool
     */
    public function getIsArchivedAttribute()
    {
        if ($this->archives->count()) {
            return true;
        }
        return false;
    }

    /**
     * Create new replies for the Task
     *
     * @param array $attributes
     * @return \Coderstm\Models\Task\Reply
     */
    public function createReply(array $attributes = [])
    {
        return $this->replies()->create($attributes);
    }

    public function getUsers()
    {
        return implode(', ', $this->users->map(function ($user) {
            return $user->name;
        })->all());
    }

    /**
     * Scope a query to only include onlyOwner
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyOwner($query)
    {
        if (current_user()->is_supper_admin) {
            return $query;
        }
        return $query->whereHas('user', function ($q) {
            $q->where('id', current_user()->id);
        })->orWhereHas('users', function ($q) {
            $q->where('id', current_user()->id);
        });
    }

    /**
     * Scope a query to only include onlyActive
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyActive($query)
    {
        return $query->doesntHave('archives');
    }

    /**
     * Scope a query to only include onlyArchived
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyArchived($query)
    {
        return $query->has('archives');
    }

    /**
     * Scope a query to only include onlyStatus
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|null $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyStatus($query, $status = null)
    {
        switch ($status) {
            case 'Live':
                return $query->onlyActive();
                break;

            case 'Archive':
                return $query->onlyArchived();
                break;
        }

        return $query;
    }

    /**
     * Scope a query to only include sortBy
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBy($query, $column = 'created_at', $direction = 'asc')
    {
        switch ($column) {
            case 'last_reply':
                return $query->select("tasks.*")
                    ->leftJoin('task_replies', function ($join) {
                        $join->on('task_replies.task_id', '=', "tasks.id");
                    })
                    ->groupBy("tasks.id")
                    ->orderBy(DB::raw('task_replies.created_at IS NULL'), 'desc')
                    ->orderBy(DB::raw('task_replies.created_at'), $direction ?? 'asc');
                break;

            default:
                return $query->orderBy($column ?: 'created_at', $direction ?? 'asc');
                break;
        }
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = AppStatus::PENDING->value;
            }
            if (empty($model->user_id)) {
                $model->user_id = optional(current_user())->id;
            }
        });
    }
}
