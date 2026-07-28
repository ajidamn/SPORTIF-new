<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
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
            'type' => 'ticket_created',
            'ticket_id' => $this->ticket->id,
            'kode_tiket' => $this->ticket->kode_tiket,
            'title' => 'Tiket Aduan Baru',
            'message' => "Ada tiket aduan baru [{$this->ticket->kode_tiket}] dari " . ($this->ticket->user->name ?? 'User'),
            'url' => route('admin.aduan.show', $this->ticket->id),
            'icon' => 'bi-ticket-detailed',
            'color' => 'primary'
        ];
    }
}
