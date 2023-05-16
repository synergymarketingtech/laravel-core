<?php

namespace Coderstm\Core\Models;

use Coderstm\Core\Traits\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use Core;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'schedule_id',
        'is_stand_by',
        'attendence',
        'status',
        'source',
        'canceled_at',
        'schedules_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_stand_by' => 'boolean',
        'attendence' => 'boolean',
        'status' => 'boolean',
        'source' => 'boolean',
        'canceled_at' => 'datetime:d M, Y \a\t h:i a',
        'schedules_at' => 'datetime:d M, Y',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
        'updated_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'same_day_canceled',
        'cancelable',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'user',
    ];

    /**
     * Get the same_day_canceled
     *
     * @return bool
     */
    public function getSameDayCanceledAttribute()
    {
        return !is_null($this->canceled_at) && $this->canceled_at->dayName == $this->created_at->dayName;
    }

    /**
     * Get the cancelable
     *
     * @return bool
     */
    public function getCancelableAttribute()
    {
        $diffInHours = now()->diffInHours($this->schedules_at, false);
        if (is_null($this->canceled_at) && $this->schedules_at->isToday() && $diffInHours < 5 && $diffInHours > 0) {
            return 'late-cancellation';
        }
        return is_null($this->canceled_at) && $this->schedules_at->gte(now());
    }

    /**
     * Get the user that owns the Booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedule that owns the Booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    /**
     * Scope a query to only include sortByUser
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByUser($query)
    {
        return $query->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->select('*', 'bookings.id as id')
            ->orderBy('users.first_name', 'asc');
    }

    /**
     * Scope a query to only include onlyActive
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyActive($query)
    {
        return $query->onlyNotCanceled()->where('is_stand_by', 0);
    }

    /**
     * Scope a query to only include onlyNotCanceled
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyNotCanceled($query)
    {
        return $query->whereNull('canceled_at');
    }

    /**
     * Scope a query to only include onlyAttended
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyAttended($query)
    {
        return $query->onlyNotCanceled()
            ->whereHas('schedule', function ($q) {
                $q->whereNotNull('sign_off_at');
            })
            ->where('attendence', 1);
    }

    /**
     * Scope a query to only include onlyNotAttended
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyNotAttended($query)
    {
        return $query->where('attendence', 0);
    }

    /**
     * Scope a query to only include onlyCanceled
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyCanceled($query)
    {
        return $query->whereNotNull('canceled_at');
    }

    /**
     * Scope a query to only include onlyStandBy
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyStandBy($query)
    {
        return $query->onlyNotCanceled()->where('is_stand_by', 1);
    }

    /**
     * Scope a query to only include onlyNoShow
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyNoShow($query)
    {
        return $query->onlyNotCanceled()
            ->onlyActive()
            ->where('attendence', 0)
            ->whereHas('schedule', function ($q) {
                $q->whereNotNull('sign_off_at');
            });
    }

    /**
     * Scope a query to only include onlyLastWeekNoShow
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyLastWeekNoShow($query)
    {
        return $query->onlyNoShow()
            ->whereHas('schedule', function ($schedule) {
                $schedule->whereRaw('date_at BETWEEN ? AND ?', [now()->subDays(7)->format('Y-m-d'), now()->format('Y-m-d')])
                    ->whereNotNull('sign_off_at');
            });
    }

    /**
     * Scope a query to only include onlyLateCancellation
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyLateCancellation($query)
    {
        return $query->onlyActive()
            ->whereNotNull('canceled_at')
            ->whereRaw("DATE(schedules_at) = DATE(canceled_at)")
            ->whereRaw("TIMESTAMPDIFF(HOUR, canceled_at, schedules_at) < 5");
    }

    /**
     * Scope a query to only include onlyLastWeekLateCancellation
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyLastWeekLateCancellation($query)
    {
        return $query->onlyLateCancellation()
            ->whereBetween('canceled_at', [now()->subDays(7), now()]);
    }

    public function cancel()
    {
        return $this->update([
            'canceled_at' => now()
        ]);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->schedules_at)) {
                $model->schedules_at = $model->schedule->date_at;
            }
        });
    }
}
