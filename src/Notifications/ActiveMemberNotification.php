<?php

namespace Coderstm\Core\Notifications;

use Coderstm\Core\Models\Core\Log;
use Coderstm\Core\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ActiveMemberNotification extends Notification
{
    use Queueable;

    public $user;
    public $subject;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Log $log)
    {
        $user = $log->logable;
        $this->user = $user;
        $plan = optional($user->plan)->label;
        $price = optional($user->plan)->fee ?? 0;
        $admin = optional($log->admin)->name ?? 'System';
        $this->subject = "{$admin} signed up {$user->name} {$log->date_time} {$plan}";
        $this->message .= "<p>{$user->email}, {$user->phone_number}";
        $this->message .= "<br>Plan: {$plan}";
        $this->message .= "<br>Price: £{$price}";
        $this->message .= "<br>Note: {$user->note}</p>";
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
