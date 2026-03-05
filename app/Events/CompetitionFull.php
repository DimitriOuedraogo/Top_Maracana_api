<?php

namespace App\Events;

use App\Models\Competition;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitionFull
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Competition $competition
    ) {}
}