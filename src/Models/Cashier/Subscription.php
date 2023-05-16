<?php

namespace App\Models\Cashier;

use App\Models\Plan\Price;
use App\Traits\HasFeature;
use App\Events\Cashier\SubscriptionProcessed;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    use HasFeature;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'price.plan',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => SubscriptionProcessed::class,
        'updated' => SubscriptionProcessed::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_valid'];

    /**
     * Get the price that owns the Subscription
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'stripe_price', 'stripe_id');
    }


    /**
     * Get is valid for the Subscription
     *
     * @return bool
     */
    public function getIsValidAttribute()
    {
        return $this->valid() ?: false;
    }
}
