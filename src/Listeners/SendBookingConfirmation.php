<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\BookingCreated;
use Coderstm\Core\Notifications\BookingConfirmationNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendBookingConfirmation implements ShouldQueue
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
     * @param  \Coderstm\Core\Events\BookingCreated  $event
     * @return void
     */
    public function handle(BookingCreated $event)
    {
        $event->booking->user->notify(new BookingConfirmationNotification($event->booking, $event->standby));
    }
}
