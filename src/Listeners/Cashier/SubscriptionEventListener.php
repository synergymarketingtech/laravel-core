<?php

namespace CoderstmCore\Listeners\Cashier;

use CoderstmCore\Events\Cashier\SubscriptionProcessed;
use CoderstmCore\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionEventListener
{
    /**
     * Handle received Cashier webhooks.
     *
     * @param  \CoderstmCore\Events\Cashier\SubscriptionProcessed  $event
     * @return void
     */
    public function handle(SubscriptionProcessed $event)
    {
        $subscription = $event->subscription;
        Invoice::createFromStripe($subscription->latestInvoice(), [
            'subscription_id' => $subscription->id
        ]);
    }
}
