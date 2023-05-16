<?php

namespace Coderstm\Core\Models\Plan;

use Carbon\Carbon;
use Coderstm\Core\Models\Plan;
use Coderstm\Core\Services\Period;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feature extends Model
{
    use HasFactory, HasSlug;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plan_features';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'slug',
        'plan_id',
        'description',
        'value',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('label')
            ->doNotGenerateSlugsOnUpdate()
            ->allowDuplicateSlugs()
            ->saveSlugsTo('slug');
    }

    /**
     * Get the plan that owns the Feature
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get feature's reset date.
     *
     * @param \Carbon\Carbon $dateFrom
     * @param string $interval
     *
     * @return \Carbon\Carbon
     */
    public function getResetDate(Carbon $dateFrom, string $interval = 'month'): Carbon
    {
        $period = new Period($interval, 1, $dateFrom ?? now());

        return $period->getEndDate();
    }
}
