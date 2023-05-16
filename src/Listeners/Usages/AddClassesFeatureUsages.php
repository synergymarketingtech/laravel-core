<?php

namespace Coderstm\Core\Listeners\Usages;

use Coderstm\Core\Events\BookingCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddClassesFeatureUsages
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
        $user = $event->booking->user;
        if ($user && !$event->standby) {
            $user->subscription()->recordFeatureUsage('classes');
        }
    }
}
