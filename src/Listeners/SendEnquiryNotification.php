<?php

namespace Coderstm\Listeners;

use Coderstm\Events\EnquiryCreated;
use Illuminate\Queue\InteractsWithQueue;
use Coderstm\Notifications\EnquiryNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendEnquiryNotification implements ShouldQueue
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
     * @param  \Coderstm\Events\EnquiryCreated  $event
     * @return void
     */
    public function handle(EnquiryCreated $event)
    {
        adminNotify(new EnquiryNotification($event->enquiry));
    }
}
