<?php

namespace CoderstmCore\Listeners;

use CoderstmCore\Events\EnquiryCreated;
use Illuminate\Queue\InteractsWithQueue;
use CoderstmCore\Notifications\EnquiryNotification;
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
     * @param  \CoderstmCore\Events\EnquiryCreated  $event
     * @return void
     */
    public function handle(EnquiryCreated $event)
    {
        admin_notify(new EnquiryNotification($event->enquiry));
    }
}
