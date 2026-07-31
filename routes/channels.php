<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('competition.{competitionId}', function () {
    return true;
});

Broadcast::channel('match.{matchId}', function () {
    return true;
});

Broadcast::channel('team.{teamId}', function ($user, $teamId) {
    return $user->team && $user->team->id === $teamId;
});
