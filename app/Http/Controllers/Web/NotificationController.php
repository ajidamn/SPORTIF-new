<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Redirect to the URL stored in the notification data if present
        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return redirect()->back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    public function fetch()
    {
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $notifications = auth()->user()->unreadNotifications()->limit(10)->get()->map(function($notif) {
            return [
                'id' => $notif->id,
                'color' => $notif->data['color'] ?? 'primary',
                'icon' => $notif->data['icon'] ?? 'bi-bell-fill',
                'title' => $notif->data['title'] ?? 'Pemberitahuan',
                'message' => $notif->data['message'] ?? '',
                'time' => $notif->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }
}
