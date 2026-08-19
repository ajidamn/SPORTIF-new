<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Notifications\Events\NotificationSent;
use App\Events\NewNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (NotificationSent $event) {
            $notification = $event->notification;
            $notifiable = $event->notifiable;
            
            // Kita hanya broadcast jika channel notifikasi mendukung array (dimana toArray() digunakan)
            if (method_exists($notification, 'toArray') && $notifiable instanceof \App\Models\User) {
                $data = $notification->toArray($notifiable);
                $unreadCount = $notifiable->unreadNotifications()->count();
                
                // Tambahkan ID notifikasi yang baru disimpan di DB
                $data['id'] = $event->response ? $event->response->id : null;
                $data['time'] = 'Baru saja';
                
                event(new NewNotification($notifiable->id, $data, $unreadCount));
            }
        });
    }
}
