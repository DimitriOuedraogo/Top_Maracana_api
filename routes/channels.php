<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('competition.{competitionId}', function () {
    return true;
});

Broadcast::channel('match.{matchId}', function () {
    return true;
});

Broadcast::channel('team.{teamId}', function ($user, $teamId) {
    return $user->teams()->where('id', $teamId)->exists();
});

Broadcast::channel('organizer.{organizerId}', function ($user, $organizerId) {
    return $user->id === $organizerId;
});
