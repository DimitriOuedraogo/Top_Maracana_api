<?php

namespace App\Services;

use App\Events\NotificationReceived;
use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function create(string $userId, string $type, string $title, string $body, array $data, string $broadcastChannel): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        broadcast(new NotificationReceived($notification, $broadcastChannel))->toOthers();

        return $notification;
    }

    public function listForUser(): array
    {
        $notifications = AppNotification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return ['notifications' => $notifications];
    }

    public function unreadCount(): array
    {
        $count = AppNotification::where('user_id', Auth::id())
            ->unread()
            ->count();

        return ['count' => $count];
    }

    public function markRead(string $id): array
    {
        $notification = AppNotification::where('user_id', Auth::id())->findOrFail($id);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return ['notification' => $notification->fresh()];
    }

    public function markAllRead(): array
    {
        $updated = AppNotification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return ['updated' => $updated];
    }
}
