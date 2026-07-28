<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $ticket;
    public $replierName;

    public function __construct($ticket, $replierName)
    {
        $this->ticket = $ticket;
        $this->replierName = $replierName;
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

    // toMail removed
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_replied',
            'ticket_id' => $this->ticket->id,
            'kode_tiket' => $this->ticket->kode_tiket,
            'title' => 'Balasan Baru',
            'message' => "Ada pesan baru di tiket [{$this->ticket->kode_tiket}] dari {$this->replierName}.",
            'url' => route('admin.aduan.show', $this->ticket->id),
            'icon' => 'bi-chat-dots',
            'color' => 'info'
        ];
    }
}
