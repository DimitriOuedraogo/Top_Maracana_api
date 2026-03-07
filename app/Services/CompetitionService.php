<?php

namespace App\Services;

use App\Models\Competition;
use App\Repositories\CompetitionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompetitionService
{
    public function __construct(
        protected CompetitionRepository $competitionRepository
    ) {
    }

    public function getPublicCompetitions(): array
    {
        $competitions = $this->competitionRepository->getPublic();
        return ['competitions' => $competitions];
    }

    public function getMyCompetitions(): array
    {
        $competitions = $this->competitionRepository->getByOrganizer(Auth::id());
        return ['competitions' => $competitions];
    }

    public function getById(string $id): array
    {
        $competition = $this->competitionRepository->findById($id);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        return ['competition' => $competition];
    }

    public function create(array $data): array
    {
        $data['organizer_id'] = Auth::id();
        $data['status'] = 'registration_open';

        // Gestion de l'image poster
        if (isset($data['poster_image'])) {
            $data['poster_image'] = $this->uploadPoster($data['poster_image']);
        }

        $competition = $this->competitionRepository->create($data);

        // Sauvegarder les jours disponibles
        if (!empty($data['days'])) {
            foreach ($data['days'] as $day) {
                $competition->days()->create(['day_of_week' => $day]);
            }
        }

        // Sauvegarder les créneaux horaires
        if (!empty($data['time_slots'])) {
            foreach ($data['time_slots'] as $slot) {
                $competition->timeSlots()->create($slot);
            }
        }

        return ['competition' => $competition->load(['days', 'timeSlots'])];
    }

    public function update(string $id, array $data): array
    {
        $competition = $this->competitionRepository->findById($id);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        if ($competition->organizer_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        // On empêche l'organisateur de changer le status manuellement
        unset($data['status']);

        if (isset($data['poster_image'])) {
            if ($competition->poster_image) {
                Storage::disk('public')->delete($competition->poster_image);
            }
            $data['poster_image'] = $this->uploadPoster($data['poster_image']);
        }

        if (isset($data['days'])) {
            $competition->days()->delete();
            foreach ($data['days'] as $day) {
                $competition->days()->create(['day_of_week' => $day]);
            }
        }

        if (isset($data['time_slots'])) {
            $competition->timeSlots()->delete();
            foreach ($data['time_slots'] as $slot) {
                $competition->timeSlots()->create($slot);
            }
        }

        $competition = $this->competitionRepository->update($competition, $data);
        return ['competition' => $competition];
    }

    public function delete(string $id): array
    {
        $competition = $this->competitionRepository->findById($id);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        if ($competition->organizer_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        if ($competition->poster_image) {
            Storage::disk('public')->delete($competition->poster_image);
        }

        $this->competitionRepository->delete($competition);
        return ['message' => 'Compétition supprimée avec succès.'];
    }

    private function uploadPoster($file): string
    {
        return $file->store('competitions/posters', 'public');
    }

    public function getKnockoutMatches(string $id): array
    {
        $competition = \App\Models\Competition::find($id);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        $matches = \App\Models\GameMatch::with(['homeTeam', 'awayTeam', 'result'])
            ->where('competition_id', $id)
            ->where('round_type', '!=', 'group')
            ->orderBy('week_number')
            ->orderBy('match_time')
            ->get()
            ->groupBy('round_type');

        return [
            'competition' => $competition->name,
            'knockout' => $matches,
        ];
    }

    public function getStatistics(string $id): array
    {
        $competition = \App\Models\Competition::with([
            'groups.teams.players',
            'matches.goals.player.team',
            'matches.cards.player.team',
        ])->find($id);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        // ── 1. Meilleur buteur ────────────────────────────────────
        $topScorer = \App\Models\MatchGoal::whereHas('match', function ($q) use ($id) {
            $q->where('competition_id', $id);
        })
            ->select('player_id', \DB::raw('COUNT(*) as goals'))
            ->groupBy('player_id')
            ->orderByDesc('goals')
            ->first();

        $topScorerData = null;
        if ($topScorer) {
            $player = \App\Models\Player::with('team')->find($topScorer->player_id);
            $topScorerData = [
                'player' => $player->full_name,
                'team' => $player->team->name,
                'goals' => $topScorer->goals,
            ];
        }

        // ── 2. Meilleur joueur (buts × 3 - jaunes × 1 - rouges × 3) ──
        $players = \App\Models\Player::whereHas('team', function ($q) use ($id) {
            $q->where('competition_id', $id);
        })->get();

        $bestPlayerData = null;
        $bestScore = null;

        foreach ($players as $player) {
            $goals = \App\Models\MatchGoal::whereHas('match', function ($q) use ($id) {
                $q->where('competition_id', $id);
            })
                ->where('player_id', $player->id)
                ->count();

            $yellowCards = \App\Models\MatchCard::whereHas('match', function ($q) use ($id) {
                $q->where('competition_id', $id);
            })
                ->where('player_id', $player->id)
                ->where('card_type', 'yellow')
                ->count();

            $redCards = \App\Models\MatchCard::whereHas('match', function ($q) use ($id) {
                $q->where('competition_id', $id);
            })
                ->where('player_id', $player->id)
                ->where('card_type', 'red')
                ->count();

            $score = ($goals * 3) - ($yellowCards * 1) - ($redCards * 3);

            if ($bestScore === null || $score > $bestScore) {
                $bestScore = $score;
                $bestPlayerData = [
                    'player' => $player->full_name,
                    'team' => $player->team->name,
                    'goals' => $goals,
                    'yellow_cards' => $yellowCards,
                    'red_cards' => $redCards,
                    'score' => $score,
                ];
            }
        }

        // ── 3. Meilleur gardien ───────────────────────────────────
// Compter les buts encaissés par équipe sur TOUTE la compétition

        $teams = \App\Models\Team::where('competition_id', $id)->get();

        $bestGoalkeeperData = null;
        $minGoalsAgainst = null;

        foreach ($teams as $team) {
            // Buts encaissés en tant qu'équipe domicile
            $goalsAgainstHome = \App\Models\MatchResult::whereHas('match', function ($q) use ($id, $team) {
                $q->where('competition_id', $id)
                    ->where('home_team_id', $team->id)
                    ->where('status', 'played');
            })->sum('away_score');

            // Buts encaissés en tant qu'équipe extérieure
            $goalsAgainstAway = \App\Models\MatchResult::whereHas('match', function ($q) use ($id, $team) {
                $q->where('competition_id', $id)
                    ->where('away_team_id', $team->id)
                    ->where('status', 'played');
            })->sum('home_score');

            $totalGoalsAgainst = $goalsAgainstHome + $goalsAgainstAway;

            if ($minGoalsAgainst === null || $totalGoalsAgainst < $minGoalsAgainst) {
                $minGoalsAgainst = $totalGoalsAgainst;

                $goalkeeper = \App\Models\Player::where('team_id', $team->id)
                    ->where('is_goalkeeper', true)
                    ->first();

                if ($goalkeeper) {
                    $bestGoalkeeperData = [
                        'player' => $goalkeeper->full_name,
                        'team' => $team->name,
                        'goals_against' => $totalGoalsAgainst,
                    ];
                }
            }
        }
        return [
            'competition' => $competition->name,
            'top_scorer' => $topScorerData,
            'best_player' => $bestPlayerData,
            'best_goalkeeper' => $bestGoalkeeperData,
        ];
    }

}