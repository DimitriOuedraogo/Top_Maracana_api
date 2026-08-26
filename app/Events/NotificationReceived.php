<?php

namespace App\Events;

use App\Models\AppNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationReceived implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AppNotification $notification,
        private string $channelName
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->channelName)];
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->notification->id,
            'type'       => $this->notification->type,
            'title'      => $this->notification->title,
            'body'       => $this->notification->body,
            'data'       => $this->notification->data,
            'read_at'    => $this->notification->read_at,
            'created_at' => $this->notification->created_at,
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationReceived';
    }
}
