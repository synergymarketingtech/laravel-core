<?php

namespace Coderstm\Core\Listeners;

use Coderstm\Core\Models\User;
use Coderstm\Core\Enum\AppStatus;
use Coderstm\Core\Events\GuestPassCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class CreateEnquiryMemberByGuestPass implements ShouldQueue
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
     * @param  \Coderstm\Core\Events\GuestPassCreated  $event
     * @return void
     */
    public function handle(GuestPassCreated $event)
    {
        $user = User::updateOrCreate([
            'email' => $event->guestPass->email
        ], [
            'title' => $event->guestPass->title,
            'first_name' => $event->guestPass->first_name,
            'last_name' => $event->guestPass->last_name,
            'phone_number' => $event->guestPass->phone_number,
            'note' => $event->guestPass->note,
            'status' => AppStatus::PENDING->value,
        ]);

        if ($user->wasRecentlyCreated) {
            $user->log_options = [
                'ref' => 'Member'
            ];
            event('eloquent.created: Coderstm\Core\Models\User', $user);
            $user->logs()->create([
                'message' => "Guest Pass request from <strong>{$event->guestPass->user->name}</strong>.",
                'type' => 'guest_pass',
                'options' => [
                    'guest_pass_id' => $event->guestPass->id
                ]
            ]);
        }
    }
}
