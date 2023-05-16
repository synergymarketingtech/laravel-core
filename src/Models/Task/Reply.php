<?php

namespace Coderstm\Core\Models\Task;

use Coderstm\Core\Enum\AppStatus;
use Coderstm\Core\Models\Admin;
use Coderstm\Core\Traits\Fileable;
use Coderstm\Core\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reply extends Model
{
    use  HasFactory, Fileable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'task_replies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message',
        'task_id',
        'user_id',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'media',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'created_time'
    ];

    /**
     * Get the created_time
     *
     * @return string
     */
    public function getCreatedTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Get the task that owns the Reply
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user that owns the Reply
     */
    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id')->withOnly([]);
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = currentUser()->id ?? null;
            }
        });
        static::created(function ($model) {
            $model->task->update([
                'status' => AppStatus::ONGOING
            ]);
        });
    }
}
