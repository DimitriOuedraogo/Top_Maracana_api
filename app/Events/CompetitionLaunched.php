<?php

namespace App\Events;

use App\Models\Competition;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitionLaunched implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Competition $competition) {}

    public function broadcastOn(): array
    {
        return [new Channel('competition.' . $this->competition->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'competition_id' => $this->competition->id,
            'name'           => $this->competition->name,
            'status'         => 'ongoing',
        ];
    }

    public function broadcastAs(): string
    {
        return 'CompetitionLaunched';
    }
}
