<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\EnquiryCreated;
use Illuminate\Queue\InteractsWithQueue;
use Coderstm\Core\Notifications\EnquiryConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Coderstm\Core\Notifications\EnquirySourceNotification;

class SendEnquiryConfirmation implements ShouldQueue
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
        if (!$event->enquiry->source) {
            $event->enquiry->user->notify(new EnquirySourceNotification($event->enquiry));
        } else {
            if ($event->enquiry->user) {
                $event->enquiry->user->notify(new EnquiryConfirmation($event->enquiry));
            } else {
                Notification::route('mail', [
                    $event->enquiry->email => $event->enquiry->name
                ])->notify(new EnquiryConfirmation($event->enquiry));
            }
        }
    }
}
