<?php

namespace Coderstm\Models\Cashier;

use Coderstm\Models\Plan\Price;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;


class SubscriptionItem extends CashierSubscriptionItem
{
    /**
     * Get the price that owns the SubscriptionItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'stripe_id', 'stripe_id');
    }
}
