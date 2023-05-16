<?php

namespace App\Listeners;

use App\Enum\AppStatus;
use App\Events\UserStatusUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\HoldMemberNotification;
use App\Notifications\ActiveMemberNotification;
use App\Notifications\DeactiveMemberNotification;

class UserStatusUpdatedNotification implements ShouldQueue
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
     * @param  \App\Events\UserStatusUpdated  $event
     * @return void
     */
    public function handle(UserStatusUpdated $event)
    {
        $user = $event->log->logable;
        $current = $event->log->options['status']['current'];
        if ($current === AppStatus::DEACTIVE->value) {
            if ($user->subscription()) {
                $user->subscription()->cancelNow();
            }
            admin_notify(new DeactiveMemberNotification($event->log, $current));
        } else if ($current === AppStatus::ACTIVE) {
            admin_notify(new ActiveMemberNotification($event->log));
        } else if ($current === AppStatus::HOLD) {
            if ($user->subscription()) {
                $user->subscription()->cancelNow();
            }
        }
    }
}
