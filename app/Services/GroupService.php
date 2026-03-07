<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Group;
use Illuminate\Support\Collection;


class GroupService
{
    // Nombre fixe d'équipes par groupe
    const TEAMS_PER_GROUP = 4;

    public function generate(Competition $competition): Collection
    {
        // 1. Récupérer toutes les équipes approuvées
        $teams = $competition->registrations()
            ->where('status', 'approved')
            ->with('team')
            ->get()
            ->pluck('team')
            ->unique('id')
            ->values()     // ← Réindexer après unique
            ->shuffle();   // ← mélange aléatoire

        // 2. Diviser en groupes de 4
        $groupsOfTeams = $teams->chunk(self::TEAMS_PER_GROUP);

        // 3. Créer les groupes en base
        $groups = collect();
        $letters = range('A', 'Z'); // ['A', 'B', 'C', ...]
        $index = 0;


        foreach ($groupsOfTeams as $teamsInGroup) {
            $group = Group::create([
                'competition_id' => $competition->id,
                'name' => 'Groupe ' . $letters[$index], // ← Groupe A, Groupe B...
            ]);

            $group->teams()->attach($teamsInGroup->pluck('id'));
            $groups->push($group->load('teams'));
            $index++; // ← incrémenter l'index
        }

        return $groups;
    }
}