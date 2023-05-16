<?php

namespace Coderstm\Core\Notifications;

use Coderstm\Core\Models\GuestPass;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GuestPassNotification extends Notification
{
    use Queueable;

    public $guestPass;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(GuestPass $guestPass)
    {
        $this->guestPass = $guestPass;
        $this->message .= "<p>Guest Pass request from <strong>{$this->guestPass->user->name}</strong>.</p>";
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
            ->subject('New Guest Pass Requested')
            ->line(new HtmlString($this->message));
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
