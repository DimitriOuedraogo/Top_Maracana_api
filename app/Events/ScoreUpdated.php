<?php

namespace App\Events;

use App\Models\GameMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public GameMatch $match) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('competition.' . $this->match->competition_id),
            new Channel('match.' . $this->match->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'match_id'       => $this->match->id,
            'competition_id' => $this->match->competition_id,
            'home_team_id'   => $this->match->home_team_id,
            'away_team_id'   => $this->match->away_team_id,
            'home_score'     => $this->match->result->home_score ?? 0,
            'away_score'     => $this->match->result->away_score ?? 0,
            'status'         => $this->match->status,
        ];
    }

    public function broadcastAs(): string
    {
        return 'ScoreUpdated';
    }
}
