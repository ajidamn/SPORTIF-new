<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    // SuperAdmin bisa akses semua tiket, user biasa hanya tiket miliknya
    if ($user->hasRole('SuperAdmin')) return true;
    
    $ticket = \App\Models\Ticket::find($ticketId);
    return $ticket && (int) $ticket->user_id === (int) $user->id;
});
