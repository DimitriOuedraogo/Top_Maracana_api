<?php

namespace App\Listeners;

use App\Events\CompetitionFull;
use App\Services\GroupService;
use App\Services\MatchService;
use Illuminate\Support\Collection;
class GenerateGroupsAndMatches
{
    public function __construct(
        protected GroupService $groupService,
        protected MatchService $matchService
    ) {
    }

    public function handle(CompetitionFull $event): void
    {
        \Log::info('Listener GenerateGroupsAndMatches déclenché');

        $competition = $event->competition;

        \Log::info(
            'Groupes avant génération: ' .
            \App\Models\Group::where('competition_id', $competition->id)->count()
        );

        // 1. Générer les groupes
        $groups = $this->groupService->generate($competition);

        \Log::info(
            'Groupes après génération: ' .
            \App\Models\Group::where('competition_id', $competition->id)->count()
        );

        // 2. Générer les matchs
        $this->matchService->generate($competition, $groups);

        // 3. Passer le status à ongoing
        $competition->update(['status' => 'ongoing']);

        \Log::info('Status mis à jour: ongoing');
    }
}