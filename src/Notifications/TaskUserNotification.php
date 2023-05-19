<?php

namespace Coderstm\Notifications;

use Coderstm\Models\Admin;
use Coderstm\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskUserNotification extends Notification
{
    use Queueable;

    public $task;
    public $user;
    public $message;
    public $attachments;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task, Admin $user)
    {
        $this->task = $task;
        $this->user = $user;
        $task->message = nl2br($task->message);

        $this->message .= "<p>{$task->message}</p>";

        if (count($task->media)) {
            $this->attachments = "<p><b><small>Attachments</small></b>:<br>";
            foreach ($task->media as $media) {
                $this->attachments .= "<small><svg style=\"width:10px\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d=\"M396.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z\"/></svg><a href=\"{$media->url}\">{$media->name}</a></small><br>";
            }
            $this->attachments .= "</p>";
        }
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
            ->greeting("Hi " . optional($this->user)->first_name . ",")
            ->subject("[Task] {$this->task->subject} by {$this->task->user->name}")
            ->line(new HtmlString($this->message))
            ->action('Open Task', adminUrl("tasks/{$this->task->id}?action=edit"))
            ->line(new HtmlString($this->attachments))
            ->line(new HtmlString('<p style="color:red"><small>Please dont respond to this email, any response should be using admin portal.</small></p>'));
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
