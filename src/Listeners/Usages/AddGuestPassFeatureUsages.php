<?php

namespace Coderstm\Core\Listeners\Usages;

use Coderstm\Core\Events\GuestPassCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddGuestPassFeatureUsages
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
     * @param  \Coderstm\Core\Events\GuestPassCreated  $event
     * @return void
     */
    public function handle(GuestPassCreated $event)
    {
        $user = $event->guestPass->user;
        if ($user) {
            $user->subscription()->recordFeatureUsage('guest-pass');
        }
    }
}
