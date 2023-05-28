<?php

namespace Coderstm\Notifications;

use Coderstm\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserSignupNotification extends Notification
{
    use Queueable;

    public $user;
    public $subject;
    public $subscription;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->subject = "Welcome to " . config('app.name') . " - Your Subscription Details";
        $this->subscription = $user->subscription();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->subject($this->subject)
            ->markdown('coderstm::emails.user.signup', [
                'name' => $this->user->first_name,
                'plan' => optional($this->user->plan)->label,
                'price' => format_amount(optional($this->subscription->price)->amount * 100),
                'interval' => optional($this->subscription->price)->interval,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
