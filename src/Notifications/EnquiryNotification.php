<?php

namespace Coderstm\Core\Notifications;

use Coderstm\Core\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EnquiryNotification extends Notification
{
    use Queueable;

    public $enquiry;
    public $message;
    public $contactUs = false;
    public $salutation;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;
        $enquiry->message = nl2br($enquiry->message);
        $this->contactUs = empty($this->enquiry->subject);

        if ($this->contactUs) {
            $this->message = '<p style="color:red"><small>This email was sent through the general enquirey form</small></p>';
        }

        $this->message .= "<p><b>Name</b>: {$enquiry->name}<br>";
        $this->message .= "<b>Email</b>: {$enquiry->email}<br>";
        $this->message .= "<b>Phone</b>: {$enquiry->phone}</p>";
        $this->message .= "<p><b>Message</b>: {$enquiry->message}</p>";
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
            ->greeting(new HtmlString(''))
            ->subject($this->enquiry->subject ?? 'Contact Us')
            ->line(new HtmlString($this->message))
            ->salutation(new HtmlString(''));
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
