<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Events\MembershipCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Coderstm\Core\Notifications\MembershipNotification;

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
     * @param  \Coderstm\Core\Events\MembershipCreated  $event
     * @return void
     */
    public function handle(MembershipCreated $event)
    {
        admin_notify(new MembershipNotification($event->user));
    }
}
