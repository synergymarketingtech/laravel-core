<?php

namespace Coderstm\Traits\Cashier;

use Coderstm\Cashier\SubscriptionBuilder;
use Laravel\Cashier\Concerns\ManagesSubscriptions as CashierManagesSubscriptions;

trait ManagesSubscriptions
{
    use CashierManagesSubscriptions;

    /**
     * Begin creating a new subscription.
     *
     * @param  string  $name
     * @param  string|string[]  $prices
     * @return \Coderstm\Cashier\SubscriptionBuilder
     */
    public function newSubscription($name, $prices = [])
    {
        return new SubscriptionBuilder($this, $name, $prices);
    }

    /**
     * Get the subscribed status of the user.
     *
     * @return bool
     */
    public function getSubscribedAttribute()
    {
        return $this->subscribed() ?: false;
    }

    /**
     * Get the has cancelled status of the user.
     *
     * @return bool
     */
    public function getHasCancelledAttribute()
    {
        return $this->subscribed() ? $this->subscription()->canceled() : false;
    }
}
