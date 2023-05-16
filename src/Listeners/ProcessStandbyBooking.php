<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\BookingCreated;
use Coderstm\Core\Events\BookingCanceled;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Coderstm\Core\Notifications\BookingCanceledNotification;

class ProcessStandbyBooking implements ShouldQueue
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
        $booking = $event->booking;
        if (!$booking->is_stand_by) {
            $schedule = $booking->schedule;
            $schedule->updateStandbyBookings();
        }
    }
}
