<?php

namespace App\Models;

use App\Traits\Logable;
use App\Models\Template;
use App\Models\ClassSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeekTemplate extends Model
{
    use HasFactory, Logable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_of_week',
        'template_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'template_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_of_week' => 'datetime:Y-m-d',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'start_of_week_formated'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'template',
    ];

    /**
     * Get the template that owns the WeekTemplate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the start_of_week_formated
     *
     * @return string
     */
    public function getStartOfWeekFormatedAttribute()
    {
        return $this->start_of_week->format('d/m/Y');
    }

    /**
     * Scope a query to only include onlyActive
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyActive($query)
    {
        return $query->where('start_of_week', '>=', now()->startOfWeek());
    }

    public static function assignClassSchedule(array $weeks = [])
    {
        collect($weeks)->each(function ($item) {
            $weekTemplate = static::updateOrCreate([
                'start_of_week' => $item['start_of_week'],
            ], [
                'template_id' => isset($item['template']['id']) ? $item['template']['id'] : null
            ]);
            if ($weekTemplate->wasRecentlyCreated || $weekTemplate->wasChanged('template_id')) {
                ClassSchedule::has('template')->where('start_of_week', $item['start_of_week'])->whereNull('sign_off_at')->forceDelete();
                if (isset($item['template']['id'])) {
                    $template = Template::find($item['template']['id']);
                    $template->schedules()->each(function ($schedule) use ($item) {
                        ClassSchedule::create([
                            'day' => $schedule->day ? $schedule->day->value : null,
                            'start_of_week' => $item['start_of_week'],
                            'start_at' => $schedule->start_at,
                            'end_at' => $schedule->end_at,
                            'class_id' => $schedule->class_id,
                            'location_id' => $schedule->location_id,
                            'instructor_id' => $schedule->instructor_id,
                            'template_id' => $schedule->template_id
                        ]);
                    });
                }
            }
        });
    }
}
