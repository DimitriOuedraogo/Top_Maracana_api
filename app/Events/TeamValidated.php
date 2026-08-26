<?php

namespace App\Events;

use App\Models\Registration;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamValidated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Registration $registration) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('team.' . $this->registration->team_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'registration_id'  => $this->registration->id,
            'competition_id'   => $this->registration->competition_id,
            'competition_name' => $this->registration->competition->name,
            'team_id'          => $this->registration->team_id,
            'status'           => 'approved',
        ];
    }

    public function broadcastAs(): string
    {
        return 'TeamValidated';
    }
}
