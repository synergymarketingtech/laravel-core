<?php

namespace Coderstm\Core\Notifications;

use Coderstm\Core\Models\Core\Log;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Stevebauman\Location\Facades\Location;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Browser;

class UserLogin extends Notification
{
    use Queueable;

    public $log;
    public $user;
    public $info;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
        $this->user = $log->logable;
        $device = $log->options['device'];
        $time = $log->options['time'];
        $location = $log->options['location'] ? "<b>Location</b>: {$log->options['location']}<br/>" : '';
        $this->info = "<b>Time</b>: {$time}<br/><b>Device</b>: {$device}<br/>{$location}<b>IP</b>: {$log->options['ip']}<br/>";
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
            ->subject('A new device has logged in to your account')
            ->greeting("Hi {$this->user->name},")
            ->line('We noticed a new sign in to your account.')
            ->line(new HtmlString($this->info))
            ->line('If this was you, you can safely ignore this email.');
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
