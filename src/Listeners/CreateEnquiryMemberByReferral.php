<?php

namespace App\Listeners;

use App\Models\User;
use App\Enum\AppStatus;
use App\Events\ReferralCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class CreateEnquiryMemberByReferral implements ShouldQueue
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
     * @param  \App\Events\ReferralCreated  $event
     * @return void
     */
    public function handle(ReferralCreated $event)
    {
        $user = User::updateOrCreate([
            'email' => $event->referral->email
        ], [
            'title' => $event->referral->title,
            'first_name' => $event->referral->first_name,
            'last_name' => $event->referral->last_name,
            'phone_number' => $event->referral->phone_number,
            'note' => $event->referral->note,
            'status' => AppStatus::PENDING->value,
        ]);

        if ($user->wasRecentlyCreated) {
            $user->log_options = [
                'ref' => 'Member'
            ];
            event('eloquent.created: App\Models\User', $user);
            $user->logs()->create([
                'message' => "<strong>{$event->referral->user->name}</strong> would like to refer a friend.",
                'type' => 'referral',
                'options' => [
                    'referral_id' => $event->referral->id
                ]
            ]);
        }
    }
}
