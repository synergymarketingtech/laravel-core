<?php

namespace Coderstm\Core\Models;

use Carbon\Carbon;
use Coderstm\Core\Traits\Core;
use Coderstm\Core\Enum\AppDay;
use Coderstm\Core\Models\Booking;
use Coderstm\Core\Traits\Bookingable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use Core, Bookingable;

    protected static $days = [
        'Tuesday' => 1,
        'Wednesday' => 2,
        'Thursday' => 3,
        'Friday' => 4,
        'Saturday' => 5,
        'Sunday' => 6,
        'Monday' => 0,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'day',
        'start_of_week',
        'start_at',
        'date_at',
        'end_at',
        'class_id',
        'location_id',
        'instructor_id',
        'template_id',
        'capacity',
        'is_active',
        'note',
        'sign_off_at',
        'admin_id',
        'remote_link',
        'remote_code',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'class',
        'location',
        'instructor',
        'admin',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'class_id',
        'location_id',
        'instructor_id',
        'template_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'has_sign_off',
        'end_at_formated',
        'start_at_formated',
        'date_at_formated',
        'has_remote',
        'time',
        'duration',
        'has_booked',
        'label',
        'bookable',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'day' => AppDay::class,
        'is_active' => 'boolean',
        'start_of_week' => 'datetime:Y-m-d',
        'date_at' => 'datetime:Y-m-d',
        'sign_off_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * Get the has_sign_off
     *
     * @return bool
     */
    public function getHasSignOffAttribute()
    {
        return !is_null($this->sign_off_at);
    }

    /**
     * Get the date_at_formated
     *
     * @return bool
     */
    public function getDateAtFormatedAttribute()
    {
        return $this->date_at->format('d/m/Y');
    }

    /**
     * Get the start_at
     *
     * @return string
     */
    public function getStartAtFormatedAttribute()
    {
        if ($this->start_at) {
            return Carbon::parse($this->start_at)->format('h:i a');
        }
        return $this->start_at;
    }

    /**
     * Get the end_at
     *
     * @return string
     */
    public function getEndAtFormatedAttribute()
    {
        if ($this->end_at) {
            return Carbon::parse($this->end_at)->format('h:i a');
        }
        return $this->end_at;
    }

    /**
     * Get the capacity
     *
     * @return string
     */
    public function getCapacityAttribute($value)
    {
        if ($this->class) {
            return $this->class->capacity;
        }
        return $value;
    }

    /**
     * Get the has remote
     *
     * @return string
     */
    public function getHasRemoteAttribute()
    {
        return !is_null($this->remote_link);
    }

    /**
     * Get the time
     *
     * @return string
     */
    public function getTimeAttribute()
    {
        return "{$this->start_at_formated} - {$this->end_at_formated}";
    }

    /**
     * Get the duration
     *
     * @return string
     */
    public function getDurationAttribute()
    {
        if ($this->start_at && $this->end_at) {
            return Carbon::parse($this->start_at)->diffInMinutes(Carbon::parse($this->end_at));
        }
        return 0;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabelAttribute()
    {
        return "{$this->id}-{$this->day->value}-{$this->date_at->format('d-m-Y')}";
    }

    /**
     * Get the has_booked
     *
     * @return string
     */
    public function getHasBookedAttribute()
    {
        return $this->total_active_bookings >= $this->capacity;
    }

    /**
     * Get the bookable
     *
     * @return string
     */
    public function getBookableAttribute()
    {
        if (now()->setTime(0, 0)->eq($this->date_at)) {
            return Carbon::parse($this->start_at)->gt(now());
        }
        return $this->date_at->lt(now()->setTime(0, 0)->addDays(7));
    }

    public function isStandby()
    {
        return $this->active_bookings()->count() >= $this->capacity;
    }

    public function isBooked($user)
    {
        return $this->bookings()->whereNull('canceled_at')->where('user_id', $user)->count() > 0;
    }

    /**
     * Get the admin that owns the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class)->withOnly([]);
    }

    /**
     * Get the class that owns the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassList::class, 'class_id');
    }

    /**
     * Get the location that owns the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the instructor that owns the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the template that owns the ClassSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Scope a query to only include whereWeek
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereWeek($query, $week = 0)
    {
        return $query->where('start_of_week', now()->addWeeks($week)->startOfWeek());
    }

    protected static function getDateAt($model)
    {
        return Carbon::parse($model->start_of_week)
            ->addDays(static::$days[$model->day->value])
            ->setTimeFromTimeString($model->start_at);
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            $model->date_at = static::getDateAt($model);
        });
        static::updating(function ($model) {
            $model->date_at = static::getDateAt($model);
        });
        static::updated(function ($model) {
            $bookings = Booking::where('schedule_id', $model->id);
            if ($model->wasChanged('schedules_at')) {
                $bookings->update([
                    'schedules_at' => $model->date_at
                ]);
            }
        });
        static::addGlobalScope('default', function (Builder $builder) {
            $builder->select("class_schedules.*")
                ->addSelect(DB::raw(
                    '(CASE
                            WHEN day = "Tuesday" THEN 1
                            WHEN day = "Wednesday" THEN 2
                            WHEN day = "Thursday" THEN 3
                            WHEN day = "Friday" THEN 4
                            WHEN day = "Saturday" THEN 5
                            WHEN day = "Sunday" THEN 6
                            ELSE 0 END
                        ) as day_index'
                ))
                ->leftJoin('instructor_class_lists', function ($join) {
                    $join->on('class_schedules.instructor_id', '=', "instructor_class_lists.instructor_id")
                        ->on('class_schedules.class_id', '=', "instructor_class_lists.class_id");
                })
                ->addSelect('instructor_class_lists.cost AS cost')
                ->withCount([
                    'bookings as no_show' => function (Builder $query) {
                        $query->whereNull('canceled_at')->where('attendence', 0);
                    },
                    'bookings as total_bookings' => function (Builder $query) {
                        $query->whereNull('canceled_at');
                    },
                    'active_bookings as total_active_bookings',
                    'stand_by_bookings as total_stand_by_bookings',
                ]);
        });
    }
}
