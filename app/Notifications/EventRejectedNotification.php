<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRejectedNotification extends Notification
{
    use Queueable;

    public $event;
    public $alasan;

    public function __construct($event, $alasan)
    {
        $this->event = $event;
        $this->alasan = $alasan;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'event_rejected',
            'title'   => 'Event Ditolak',
            'message' => "Event \"{$this->event->nama}\" ditolak. Alasan: {$this->alasan}",
            'url'     => route('admin.dashboard') . '?page=events',
            'icon'    => 'bi-x-circle',
            'color'   => 'danger',
        ];
    }
}
