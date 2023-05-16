<?php

namespace App\Listeners;

use App\Events\MembershipCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\MembershipNotification;

class SendMembershipNotification implements ShouldQueue
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
     * @param  \App\Events\MembershipCreated  $event
     * @return void
     */
    public function handle(MembershipCreated $event)
    {
        admin_notify(new MembershipNotification($event->user));
    }
}
