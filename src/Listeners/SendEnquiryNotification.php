<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\EnquiryCreated;
use Illuminate\Queue\InteractsWithQueue;
use Coderstm\Core\Notifications\EnquiryNotification;
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
     * @param  \Coderstm\Core\Events\EnquiryCreated  $event
     * @return void
     */
    public function handle(EnquiryCreated $event)
    {
        admin_notify(new EnquiryNotification($event->enquiry));
    }
}
