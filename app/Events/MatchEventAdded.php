<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchEventAdded implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $matchId,
        public string $competitionId,
        public string $eventType,
        public array  $eventData
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('match.' . $this->matchId),
            new Channel('competition.' . $this->competitionId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'match_id'       => $this->matchId,
            'competition_id' => $this->competitionId,
            'event_type'     => $this->eventType,
            'event_data'     => $this->eventData,
        ];
    }

    public function broadcastAs(): string
    {
        return 'MatchEventAdded';
    }
}
