<?php

namespace App\Listeners;

use App\Events\ReferralCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ReferralNotification;

class SendReferralNotification implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ReferralCreated  $event
     * @return void
     */
    public function handle(ReferralCreated $event)
    {
        admin_notify(new ReferralNotification($event->referral));
    }
}
