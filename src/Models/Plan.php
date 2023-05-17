<?php

namespace Coderstm\Core\Models;

use Coderstm\Core\Traits\Base;
use Coderstm\Core\Enum\PlanInterval;
use Coderstm\Core\Models\Plan\Price;
use Coderstm\Core\Models\Plan\Feature;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use Base;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'description',
        'note',
        'is_active',
        'stripe_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['feature_lines'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:d M, Y \a\t h:i a',
    ];

    /**
     * Get all of the prices for the Plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function getFeatureLinesAttribute()
    {
        return !empty($this->description) ? explode("\n", $this->description) : [];
    }

    /**
     * Get all of the features for the Plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    public function syncFeatures(Collection $items)
    {
        // delete removed features
        $this->features()->whereNotIn('id', $items->pluck('id')->filter())->delete();

        // create or updated new features
        $items->map(function ($item) {
            return (object) $item;
        })->each(function ($item) {
            $this->features()->updateOrCreate([
                'id' => optional($item)->id,
            ], [
                'label' => optional($item)->label,
                'description' => optional($item)->description,
                'value' => optional($item)->value,
            ]);
        });

        return $this;
    }

    /**
     * Override the create method to add custom functionality
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function create(array $attributes = [])
    {
        try {
            // create a product for the plan in gateway
            $product = static::createStripeProduct($attributes);

            // Call the parent create method to save the model
            $plan = (new static)->fill(collect($attributes)->only((new static)->getFillable())->toArray());
            $plan->stripe_id = $product->id;
            $plan->save();

            // Create monthly and yearly prices
            $monthlyPrice = static::createPrice($plan, $attributes['monthly_fee'], PlanInterval::MONTH);
            $yearlyPrice = static::createPrice($plan, $attributes['yearly_fee'], PlanInterval::YEAR);

            // Attach the prices to the plan
            $plan->prices()->saveMany([$monthlyPrice, $yearlyPrice]);

            return $plan;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Determine if the plan has a stripe id.
     *
     * @return bool
     */
    public function hasStripeId()
    {
        return !is_null($this->stripe_id);
    }

    public function createAsStripePlan()
    {
        if (!$this->hasStripeId()) {
            $product = static::createStripeProduct($this->toArray());
            $this->stripe_id = $product->id;
            $this->save();
        }
        return $this;
    }

    // Create a price for a given plan and amount
    protected static function createPrice($plan, $amount, $interval)
    {
        $price = Cashier::stripe()->prices->create([
            'nickname' => $plan->label,
            'product' => $plan->stripe_id,
            'unit_amount' => $amount * 100,
            'currency' => config('cashier.currency'),
            'recurring' => ['interval' => $interval->value],
        ]);

        return $plan->prices()->create([
            'amount' => $amount,
            'stripe_id' => $price->id,
            'interval' => $interval->value,
        ]);
    }

    protected static function createStripeProduct(array $attributes = [])
    {
        $optional = optional((object) $attributes);
        return Cashier::stripe()->products->create([
            'name' => $optional->label,
            'description' => $optional->description ?? "",
        ]);
    }

    protected static function booted()
    {
        parent::booted();
        static::updated(function ($model) {
            if ($model->hasStripeId()) {
                Cashier::stripe()->products->update($model->stripe_id, [
                    'name' => $model->label,
                    'description' => $model->description ?? "",
                ]);
            }
        });
        static::addGlobalScope('default', function (Builder $builder) {
            $builder->withCount([
                'prices as monthly_fee' => function (Builder $query) {
                    $query->select(DB::raw("SUM(amount) as amount_sum"))
                        ->where('interval', PlanInterval::MONTH->value);
                },
                'prices as yearly_fee' => function (Builder $query) {
                    $query->select(DB::raw("SUM(amount) as amount_sum"))
                        ->where('interval', PlanInterval::YEAR->value);
                },
            ]);
        });
    }
}
