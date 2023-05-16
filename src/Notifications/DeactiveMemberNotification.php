<?php

namespace Coderstm\Core\Notifications;

use Coderstm\Core\Models\Log;
use Coderstm\Core\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DeactiveMemberNotification extends Notification
{
    use Queueable;

    public $user;
    public $status;
    public $subject;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Log $log, $status = 'Active')
    {
        $user = $log->logable;
        $this->user = $user;
        $this->status = $status;
        $plan = $user->plan ? $user->plan->label : "";
        $this->subject = "{$log->admin->name} deactivated {$user->name} {$log->date_time} {$plan}";
        $this->message = "Status change from {$log->options['status']['previous']} to {$status}. $user->note";
        $this->message .= "<br>{$user->email}, {$user->phone_number}";
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
