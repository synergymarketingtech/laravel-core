<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\GuestPassCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Coderstm\Core\Notifications\GuestPassNotification;
use Illuminate\Support\Facades\Notification;

class SendGuestPassNotification implements ShouldQueue
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
        admin_notify(new GuestPassNotification($event->guestPass));
    }
}
