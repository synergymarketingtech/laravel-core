<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\BookingCanceled;
use Coderstm\Core\Notifications\BookingCanceledNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendBookingCanceled implements ShouldQueue
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
     * @param  \Coderstm\Core\Events\BookingCanceled  $event
     * @return void
     */
    public function handle(BookingCanceled $event)
    {
        $event->booking->user->notify(new BookingCanceledNotification($event->booking));
    }
}
