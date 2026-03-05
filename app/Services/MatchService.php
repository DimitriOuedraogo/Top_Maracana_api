<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\MatchResult;
use App\Models\MatchCard;
use Illuminate\Support\Collection;

class MatchService
{
    public function generate(Competition $competition, Collection $groups): void
    {
        // Récupérer les jours et créneaux disponibles
        $days = $competition->days->pluck('day_of_week')->toArray();
        $timeSlots = $competition->timeSlots->pluck('start_time')->toArray();
        $matchesPerDay = $competition->matches_per_day;

        // Générer tous les matchs (Round Robin) pour chaque groupe
        $allMatches = collect();

        foreach ($groups as $group) {
            $teams = $group->teams;
            $roundRobinMatches = $this->generateRoundRobin($group->id, $competition->id, $teams);
            $allMatches = $allMatches->merge($roundRobinMatches);
        }

        // Planifier les matchs (assigner semaine, jour, heure)
        $this->scheduleMatches($allMatches, $days, $timeSlots, $matchesPerDay);
    }

    // ── Ajouter un but ────────────────────────────────────────
    public function addGoal(string $matchId, array $data): array
    {
        $match = GameMatch::with(['homeTeam', 'awayTeam', 'result'])->find($matchId);

        if (!$match) {
            throw new \Exception('Match introuvable.', 404);
        }

        if ($match->status === 'played') {
            throw new \Exception('Ce match est déjà clôturé.', 400);
        }

        // Vérifier que le joueur appartient à une des deux équipes
        $player = \App\Models\Player::find($data['player_id']);

        if (!$player) {
            throw new \Exception('Joueur introuvable.', 404);
        }

        if (!in_array($player->team_id, [$match->home_team_id, $match->away_team_id])) {
            throw new \Exception('Ce joueur n\'appartient pas à ce match.', 400);
        }

        // Créer ou récupérer le résultat du match
        $result = $match->result ?? MatchResult::create([
            'match_id' => $match->id,
            'home_score' => 0,
            'away_score' => 0,
        ]);

        // Incrémenter le score de la bonne équipe
        if ($player->team_id === $match->home_team_id) {
            $result->increment('home_score');
        } else {
            $result->increment('away_score');
        }

        return [
            'message' => 'But ajouté avec succès.',
            'result' => $result->fresh(),
        ];
    }

    // ── Ajouter un carton ─────────────────────────────────────
    public function addCard(string $matchId, array $data): array
    {
        $match = GameMatch::with(['homeTeam', 'awayTeam'])->find($matchId);

        if (!$match) {
            throw new \Exception('Match introuvable.', 404);
        }

        if ($match->status === 'played') {
            throw new \Exception('Ce match est déjà clôturé.', 400);
        }

        // Vérifier que le joueur appartient à une des deux équipes
        $player = \App\Models\Player::find($data['player_id']);

        if (!$player) {
            throw new \Exception('Joueur introuvable.', 404);
        }

        if (!in_array($player->team_id, [$match->home_team_id, $match->away_team_id])) {
            throw new \Exception('Ce joueur n\'appartient pas à ce match.', 400);
        }

        // Vérifier si le joueur a déjà un carton rouge
        $hasRedCard = MatchCard::where('match_id', $matchId)
            ->where('player_id', $data['player_id'])
            ->where('card_type', 'red')
            ->exists();

        if ($hasRedCard) {
            throw new \Exception('Ce joueur a déjà reçu un carton rouge.', 400);
        }

        // Créer le carton
        $card = MatchCard::create([
            'match_id' => $matchId,
            'player_id' => $data['player_id'],
            'card_type' => $data['card_type'],
            'minute' => $data['minute'],
        ]);

        return [
            'message' => 'Carton ajouté avec succès.',
            'card' => $card->load('player'),
        ];
    }

    // ── Clôturer le match ─────────────────────────────────────
    public function closeMatch(string $matchId): array
    {
        $match = GameMatch::with(['homeTeam', 'awayTeam', 'result'])->find($matchId);

        if (!$match) {
            throw new \Exception('Match introuvable.', 404);
        }

        if ($match->status === 'played') {
            throw new \Exception('Ce match est déjà clôturé.', 400);
        }

        // Récupérer ou créer le résultat (0-0 si aucun but)
        $result = $match->result ?? MatchResult::create([
            'match_id' => $match->id,
            'home_score' => 0,
            'away_score' => 0,
        ]);

        $homeScore = $result->home_score;
        $awayScore = $result->away_score;

        // Mettre à jour le classement des deux équipes
        $this->updateStandings($match, $homeScore, $awayScore);

        // Passer le match à "played"
        $match->update(['status' => 'played']);

        return [
            'message' => 'Match clôturé avec succès.',
            'home_team' => $match->homeTeam->name,
            'away_team' => $match->awayTeam->name,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'result' => $homeScore > $awayScore
                ? $match->homeTeam->name . ' gagne'
                : ($homeScore < $awayScore
                    ? $match->awayTeam->name . ' gagne'
                    : 'Match nul'),
        ];
    }

    // ── Mettre à jour le classement ───────────────────────────
    private function updateStandings(GameMatch $match, int $homeScore, int $awayScore): void
    {
        $groupId = $match->group_id;
        $homeTeamId = $match->home_team_id;
        $awayTeamId = $match->away_team_id;

        if ($homeScore > $awayScore) {
            // Home team gagne
            $this->updateTeamStats($groupId, $homeTeamId, 'win', $homeScore, $awayScore);
            $this->updateTeamStats($groupId, $awayTeamId, 'loss', $awayScore, $homeScore);
        } elseif ($homeScore < $awayScore) {
            // Away team gagne
            $this->updateTeamStats($groupId, $awayTeamId, 'win', $awayScore, $homeScore);
            $this->updateTeamStats($groupId, $homeTeamId, 'loss', $homeScore, $awayScore);
        } else {
            // Nul
            $this->updateTeamStats($groupId, $homeTeamId, 'draw', $homeScore, $awayScore);
            $this->updateTeamStats($groupId, $awayTeamId, 'draw', $awayScore, $homeScore);
        }
    }

    // ── Mettre à jour les stats d'une équipe ──────────────────
    private function updateTeamStats(
        string $groupId,
        string $teamId,
        string $result,
        int $goalsFor,
        int $goalsAgainst
    ): void {
        $points = match ($result) {
            'win' => 3,
            'draw' => 1,
            'loss' => 0,
        };

        $wins = $result === 'win' ? 1 : 0;
        $draws = $result === 'draw' ? 1 : 0;
        $losses = $result === 'loss' ? 1 : 0;

        \DB::table('group_team')
            ->where('group_id', $groupId)
            ->where('team_id', $teamId)
            ->update([
                'played' => \DB::raw('played + 1'),
                'win' => \DB::raw("win + $wins"),
                'draws' => \DB::raw("draws + $draws"),
                'losses' => \DB::raw("losses + $losses"),
                'goals_for' => \DB::raw("goals_for + $goalsFor"),
                'goals_against' => \DB::raw("goals_against + $goalsAgainst"),
                'goal_difference' => \DB::raw("goal_difference + " . ($goalsFor - $goalsAgainst)),
                'points' => \DB::raw("points + $points"),
            ]);
    }


    // ── Générer les matchs Round Robin ────────────────────────
    private function generateRoundRobin(string $groupId, string $competitionId, $teams): Collection
    {
        $matches = collect();
        $teamsList = $teams->values();
        $count = $teamsList->count();

        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $matches->push([
                    'competition_id' => $competitionId,
                    'group_id' => $groupId,
                    'home_team_id' => $teamsList[$i]->id,
                    'away_team_id' => $teamsList[$j]->id,
                    'round_type' => 'group',
                    'status' => 'scheduled',
                ]);
            }
        }

        return $matches;
    }

    // ── Planifier les matchs ───────────────────────────────────
    private function scheduleMatches(
        Collection $matches,
        array $days,
        array $timeSlots,
        int $matchesPerDay
    ): void {
        $pendingMatches = $matches->values()->toArray();
        $scheduledTeamsPerDay = []; // [dayKey => [team_ids]]

        $weekNumber = 1;
        $dayIndex = 0;
        $maxWeeks = 20; // sécurité pour éviter boucle infinie

        while (!empty($pendingMatches) && $weekNumber <= $maxWeeks) {
            $currentDay = $days[$dayIndex];
            $dayKey = $weekNumber . '_' . $currentDay;

            // Récupérer les équipes du jour précédent
            if ($dayIndex > 0) {
                $prevDayKey = $weekNumber . '_' . $days[$dayIndex - 1];
            } elseif ($weekNumber > 1) {
                $prevDayKey = ($weekNumber - 1) . '_' . $days[count($days) - 1];
            } else {
                $prevDayKey = null;
            }

            $teamsToday = $scheduledTeamsPerDay[$dayKey] ?? [];
            $teamsPrevDay = $prevDayKey ? ($scheduledTeamsPerDay[$prevDayKey] ?? []) : [];

            $scheduledThisDay = 0;
            $slotIndex = 0;

            foreach ($pendingMatches as $key => $match) {
                // Si on a atteint le max de matchs par jour → stop
                if ($scheduledThisDay >= $matchesPerDay)
                    break;
                if ($slotIndex >= count($timeSlots))
                    break;

                $homeTeam = $match['home_team_id'];
                $awayTeam = $match['away_team_id'];

                // ❌ Vérification 1 : équipe joue déjà ce jour
                $sameDayConflict = in_array($homeTeam, $teamsToday)
                    || in_array($awayTeam, $teamsToday);

                // ❌ Vérification 2 : équipe a joué le jour précédent
                $consecutiveConflict = in_array($homeTeam, $teamsPrevDay)
                    || in_array($awayTeam, $teamsPrevDay);

                if ($sameDayConflict || $consecutiveConflict) {
                    continue; // essayer le match suivant
                }

                // ✅ Planifier ce match
                $match['week_number'] = $weekNumber;
                $match['day_of_week'] = $currentDay;
                $match['match_time'] = $timeSlots[$slotIndex];

                GameMatch::create($match);

                // Mettre à jour les équipes du jour
                $teamsToday[] = $homeTeam;
                $teamsToday[] = $awayTeam;
                $scheduledTeamsPerDay[$dayKey] = $teamsToday;

                unset($pendingMatches[$key]);
                $scheduledThisDay++;
                $slotIndex++;
            }

            // Réindexer les matchs restants
            $pendingMatches = array_values($pendingMatches);

            // Passer au jour suivant
            $dayIndex++;
            if ($dayIndex >= count($days)) {
                $dayIndex = 0;
                $weekNumber++;
            }
        }
    }

}
