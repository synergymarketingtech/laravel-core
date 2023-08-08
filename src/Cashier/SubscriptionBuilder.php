<?php

namespace Coderstm\Cashier;

use Exception;
use Laravel\Cashier\SubscriptionBuilder as CashierSubscriptionBuilder;

class SubscriptionBuilder extends CashierSubscriptionBuilder
{
    /**
     * Create a new Stripe subscription.
     *
     * @param  \Stripe\PaymentMethod|string|null  $paymentMethod
     * @param  array  $customerOptions
     * @param  array  $subscriptionOptions
     * @return \Laravel\Cashier\Subscription
     *
     * @throws \Exception
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     */
    public function create($paymentMethod = null, array $customerOptions = [], array $subscriptionOptions = [])
    {
        if (empty($this->items)) {
            throw new Exception('At least one price is required when starting subscriptions.');
        }

        $stripeCustomer = $this->getStripeCustomer($paymentMethod, $customerOptions);

        $stripeSubscription = $this->owner->stripe()->subscriptions->create(array_merge(
            ['customer' => $stripeCustomer->id],
            $this->buildPayload(),
            $subscriptionOptions
        ));

        $subscription = $this->createSubscription($stripeSubscription);
        $subscription->fill($this->metadata)->save();

        $this->handlePaymentFailure($subscription, $paymentMethod);

        return $subscription;
    }
}
