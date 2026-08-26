<?php

namespace App\Events;

use App\Models\Registration;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRegistration implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Registration $registration) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organizer.' . $this->registration->competition->organizer_id),
        ];
    }

    public function broadcastWith(): array
    {
        $team = $this->registration->team;

        return [
            'registration_id'  => $this->registration->id,
            'competition_id'   => $this->registration->competition_id,
            'status'           => 'pending',
            'received_at'      => $this->registration->created_at,
            'team' => [
                'id'             => $team->id,
                'name'           => $team->name,
                'logo'           => $team->logo,
                'players_count'  => $this->registration->players()->count(),
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewRegistration';
    }
}
