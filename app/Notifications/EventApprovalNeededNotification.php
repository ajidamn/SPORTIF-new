<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventApprovalNeededNotification extends Notification
{
    use Queueable;

    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $kreator = $this->event->creator ? $this->event->creator->name : 'User';
        return [
            'type'    => 'event_approval_needed',
            'title'   => 'Event Perlu Persetujuan',
            'message' => "Event \"{$this->event->nama}\" dari {$kreator} menunggu persetujuan Anda.",
            'url'     => route('admin.dashboard') . '?page=events',
            'icon'    => 'bi-calendar-check',
            'color'   => 'warning',
        ];
    }
}
