<?php

namespace App\Listeners\Cashier;

use App\Events\Cashier\SubscriptionProcessed;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionEventListener
{
    /**
     * Handle received Cashier webhooks.
     *
     * @param  \App\Events\Cashier\SubscriptionProcessed  $event
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
