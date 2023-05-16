<?php

namespace Coderstm\Core\Listeners\Cashier;

use Coderstm\Core\Events\Cashier\SubscriptionProcessed;
use Coderstm\Core\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionEventListener
{
    /**
     * Handle received Cashier webhooks.
     *
     * @param  \Coderstm\Core\Events\Cashier\SubscriptionProcessed  $event
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
