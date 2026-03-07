<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class KnockoutService
{
    // ── Générer les phases éliminatoires ──────────────────────
    public function generateKnockout(string $competitionId): array
    {
        $competition = Competition::with([
            'groups.teams',
            'matches',
        ])->find($competitionId);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        // Vérifier que c'est l'organisateur
        if ($competition->organizer_id !== Auth::id()) {
            throw new \Exception('Seul l\'organisateur peut générer les phases éliminatoires.', 403);
        }

        // Vérifier que tous les matchs de groupes sont joués
        $this->checkAllGroupMatchesPlayed($competition);

        // Vérifier que les phases éliminatoires n'ont pas déjà été générées
        $knockoutExists = GameMatch::where('competition_id', $competitionId)
            ->where('round_type', '!=', 'group')
            ->exists();

        if ($knockoutExists) {
            throw new \Exception('Les phases éliminatoires ont déjà été générées.', 400);
        }

        // Récupérer le classement de chaque groupe
        $standings = $this->getGroupStandings($competition);

        // Générer les huitièmes ou quarts selon le nombre de groupes
        $matches = $this->generateFirstKnockoutRound($competition, $standings);

        return [
            'message' => 'Phases éliminatoires générées avec succès.',
            'matches' => $matches,
        ];
    }

    // ── Vérifier que tous les matchs de groupes sont joués ────
    private function checkAllGroupMatchesPlayed(Competition $competition): void
    {
        $unplayedCount = GameMatch::where('competition_id', $competition->id)
            ->where('round_type', 'group')
            ->where('status', '!=', 'played')
            ->count();

        if ($unplayedCount > 0) {
            throw new \Exception(
                "Impossible de générer les phases éliminatoires. Il reste $unplayedCount match(s) de groupe non clôturé(s).",
                400
            );
        }
    }

    // ── Récupérer le classement final de chaque groupe ────────
    private function getGroupStandings(Competition $competition): array
    {
        $standings = [];

        foreach ($competition->groups as $group) {
            // Récupérer les équipes triées par points, différence de buts, buts marqués
            $teams = \DB::table('group_team')
                ->where('group_id', $group->id)
                ->orderByDesc('points')
                ->orderByDesc('goal_difference')
                ->orderByDesc('goals_for')
                ->get();

            $standings[$group->id] = [
                'group' => $group->name,
                'first' => $teams[0]->team_id,  // 1er du groupe
                'second' => $teams[1]->team_id,  // 2ème du groupe
            ];
        }

        return $standings;
    }

    // ── Générer le premier tour éliminatoire ──────────────────
// ── Générer le premier tour éliminatoire ──────────────────
    private function generateFirstKnockoutRound(Competition $competition, array $standings): array
    {
        $groups = array_values($standings);
        $groupCount = count($groups);

        // Déterminer le round_type selon le nombre de groupes
        $roundType = match ($groupCount) {
            2 => 'semi',
            4 => 'quarter',
            8 => 'round_of_16',
            default => 'quarter',
        };

        // Construire la liste des matchs à planifier
        $matchesToSchedule = [];

        for ($i = 0; $i < $groupCount; $i += 2) {
            $groupA = $groups[$i];
            $groupB = $groups[$i + 1];

            // Match 1 : 1er Groupe A vs 2ème Groupe B
            $matchesToSchedule[] = [
                'competition_id' => $competition->id,
                'group_id' => null,
                'home_team_id' => $groupA['first'],
                'away_team_id' => $groupB['second'],
                'round_type' => $roundType,
                'status' => 'scheduled',
            ];

            // Match 2 : 1er Groupe B vs 2ème Groupe A
            $matchesToSchedule[] = [
                'competition_id' => $competition->id,
                'group_id' => null,
                'home_team_id' => $groupB['first'],
                'away_team_id' => $groupA['second'],
                'round_type' => $roundType,
                'status' => 'scheduled',
            ];
        }

        return $this->scheduleKnockoutMatches($competition, $matchesToSchedule);
    }

    // ── Planifier les matchs éliminatoires ───────────────────
    private function scheduleKnockoutMatches(Competition $competition, array $matchesToSchedule): array
    {
        $days = $competition->days->pluck('day_of_week')->toArray();
        $timeSlots = $competition->timeSlots->pluck('start_time')->toArray();
        $matchesPerDay = $competition->matches_per_day;

        // Trouver la dernière semaine utilisée
        $lastWeek = GameMatch::where('competition_id', $competition->id)
            ->max('week_number') ?? 0;

        $weekNumber = $lastWeek + 1; // ← continuer la numérotation
        $dayIndex = 0;
        $scheduledThisDay = 0;
        $slotIndex = 0;
        $createdMatches = [];

        foreach ($matchesToSchedule as $match) {
            // Si on a atteint le max de matchs par jour → jour suivant
            if ($scheduledThisDay >= $matchesPerDay) {
                $dayIndex++;
                $scheduledThisDay = 0;
                $slotIndex = 0;

                // Si on a épuisé les jours → semaine suivante
                if ($dayIndex >= count($days)) {
                    $dayIndex = 0;
                    $weekNumber++;
                }
            }

            $match['week_number'] = $weekNumber;
            $match['day_of_week'] = $days[$dayIndex];
            $match['match_time'] = $timeSlots[$slotIndex];

            $created = GameMatch::create($match);
            $createdMatches[] = $created->load(['homeTeam', 'awayTeam']);

            $scheduledThisDay++;
            $slotIndex++;
        }

        return $createdMatches;
    }

    // ── Générer le tour suivant après clôture d'un tour ───────
    public function generateNextRound(string $competitionId): array
    {
        $competition = Competition::find($competitionId);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        if ($competition->organizer_id !== Auth::id()) {
            throw new \Exception('Seul l\'organisateur peut générer le tour suivant.', 403);
        }

        // Trouver le dernier round joué
        $lastRound = GameMatch::where('competition_id', $competitionId)
            ->where('round_type', '!=', 'group')
            ->whereIn('round_type', ['round_of_16', 'quarter', 'semi'])
            ->orderByDesc('created_at')
            ->value('round_type');

        if (!$lastRound) {
            throw new \Exception('Aucune phase éliminatoire trouvée.', 404);
        }

        // Vérifier que tous les matchs du dernier round sont joués
        $unplayed = GameMatch::where('competition_id', $competitionId)
            ->where('round_type', $lastRound)
            ->where('status', '!=', 'played')
            ->count();

        if ($unplayed > 0) {
            throw new \Exception(
                "Il reste $unplayed match(s) non clôturé(s) pour ce tour.",
                400
            );
        }

        // Déterminer le prochain round
        $nextRound = match ($lastRound) {
            'round_of_16' => 'quarter',
            'quarter' => 'semi',
            'semi' => 'final',
            default => throw new \Exception('La compétition est terminée.', 400),
        };

        // Vérifier que le prochain round n'existe pas déjà
        $nextRoundExists = GameMatch::where('competition_id', $competitionId)
            ->where('round_type', $nextRound)
            ->exists();

        if ($nextRoundExists) {
            throw new \Exception(
                'Les matchs de ce tour ont déjà été générés.',
                400
            );
        }

        // Récupérer les gagnants du dernier round
        $winners = $this->getRoundWinners($competitionId, $lastRound);

        // Générer les matchs du prochain round
        $matchesToSchedule = [];

        for ($i = 0; $i < count($winners); $i += 2) {
            $matchesToSchedule[] = [
                'competition_id' => $competitionId,
                'group_id' => null,
                'home_team_id' => $winners[$i],
                'away_team_id' => $winners[$i + 1],
                'round_type' => $nextRound,
                'status' => 'scheduled',
            ];
        }

        $matches = $this->scheduleKnockoutMatches($competition, $matchesToSchedule);


        return [
            'message' => 'Tour suivant généré : ' . strtoupper($nextRound),
            'round' => $nextRound,
            'matches' => $matches,
        ];
    }

    // ── Récupérer les gagnants d'un round ─────────────────────
    private function getRoundWinners(string $competitionId, string $round): array
    {
        $matches = GameMatch::with('result')
            ->where('competition_id', $competitionId)
            ->where('round_type', $round)
            ->where('status', 'played')
            ->get();

        $winners = [];

        foreach ($matches as $match) {
            $result = $match->result;

            if ($result->home_score > $result->away_score) {
                $winners[] = $match->home_team_id;
            } elseif ($result->away_score > $result->home_score) {
                $winners[] = $match->away_team_id;
            } else {
                // Match nul → vérifier les tirs au but
                if ($result->home_penalty_score > $result->away_penalty_score) {
                    $winners[] = $match->home_team_id;
                } elseif ($result->away_penalty_score > $result->home_penalty_score) {
                    $winners[] = $match->away_team_id;
                } else {
                    throw new \Exception(
                        "Le match entre {$match->homeTeam->name} et {$match->awayTeam->name} est nul. Saisissez les tirs au but.",
                        400
                    );
                }
            }
        }

        return $winners;
    }

    // ── Saisir les tirs au but ────────────────────────────────
    public function addPenalties(string $matchId, array $data): array
    {
        $match = GameMatch::with(['homeTeam', 'awayTeam', 'result', 'competition'])->find($matchId);

        if (!$match) {
            throw new \Exception('Match introuvable.', 404);
        }

        if ($match->competition->organizer_id !== Auth::id()) {
            throw new \Exception('Seul l\'organisateur peut saisir les tirs au but.', 403);
        }

        if ($match->round_type === 'group') {
            throw new \Exception('Les tirs au but ne sont pas autorisés en phase de groupes.', 400);
        }

        if ($match->status !== 'played') {
            throw new \Exception('Clôturez d\'abord le match avant de saisir les tirs au but.', 400);
        }

        $result = $match->result;

        if (!$result || $result->home_score !== $result->away_score) {
            throw new \Exception('Les tirs au but ne sont nécessaires qu\'en cas de match nul.', 400);
        }

        if ($data['home_penalty'] === $data['away_penalty']) {
            throw new \Exception('Les tirs au but ne peuvent pas être nuls.', 400);
        }

        $result->update([
            'home_penalty_score' => $data['home_penalty'],
            'away_penalty_score' => $data['away_penalty'],
        ]);

        $winner = $data['home_penalty'] > $data['away_penalty']
            ? $match->homeTeam->name
            : $match->awayTeam->name;

        return [
            'message' => 'Tirs au but enregistrés.',
            'home_penalty' => $data['home_penalty'],
            'away_penalty' => $data['away_penalty'],
            'winner' => $winner,
        ];
    }
}