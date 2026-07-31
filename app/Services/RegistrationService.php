<?php

namespace App\Services;

use App\Events\CompetitionFull;
use App\Events\TeamRejected;
use App\Events\TeamValidated;
use App\Models\Competition;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RegistrationService
{
    public function register(string $competitionId, string $teamId, array $playerIds): array
    {
        $competition = Competition::find($competitionId);
        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }
        if ($competition->status !== 'registration_open') {
            throw new \Exception('Les inscriptions sont fermées pour cette compétition.', 400);
        }

        $team = Team::with('players')->find($teamId);
        if (!$team) {
            throw new \Exception('Équipe introuvable.', 404);
        }
        if ($team->manager_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        $alreadyRegistered = Registration::where('competition_id', $competitionId)
            ->where('team_id', $teamId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        if ($alreadyRegistered) {
            throw new \Exception('Cette équipe est déjà inscrite dans cette compétition.', 400);
        }

        $approvedCount = $competition->registrations()->where('status', 'approved')->count();
        if ($approvedCount >= $competition->max_teams) {
            throw new \Exception('Le nombre maximum d\'équipes est atteint.', 400);
        }

        // Vérifier le nombre de joueurs sélectionnés
        if (count($playerIds) !== $competition->players_per_team) {
            throw new \Exception(
                "Vous devez sélectionner exactement {$competition->players_per_team} joueurs pour cette compétition.",
                400
            );
        }

        // Vérifier que tous les joueurs sélectionnés appartiennent à l'équipe
        $teamPlayerIds = $team->players->pluck('id')->toArray();
        $invalidPlayers = array_diff($playerIds, $teamPlayerIds);
        if (!empty($invalidPlayers)) {
            throw new \Exception('Certains joueurs sélectionnés ne font pas partie de cette équipe.', 400);
        }

        // Valider gardien et âge sur la sélection uniquement
        $selectedPlayers = $team->players->whereIn('id', $playerIds);

        $goalkeeperCount = $selectedPlayers->where('is_goalkeeper', true)->count();
        if ($goalkeeperCount === 0) {
            throw new \Exception('La sélection doit inclure au moins 1 gardien de but.', 400);
        }
        if ($goalkeeperCount > 1) {
            throw new \Exception('La sélection ne peut contenir qu\'un seul gardien de but.', 400);
        }

        foreach ($selectedPlayers as $player) {
            $age = Carbon::parse($player->birth_date)->age;
            if ($competition->age_min && $age < $competition->age_min) {
                throw new \Exception(
                    "Le joueur {$player->full_name} est trop jeune (minimum: {$competition->age_min} ans).",
                    400
                );
            }
            if ($competition->age_max && $age > $competition->age_max) {
                throw new \Exception(
                    "Le joueur {$player->full_name} est trop âgé (maximum: {$competition->age_max} ans).",
                    400
                );
            }
        }

        $registration = $competition->registrations()->create([
            'team_id' => $team->id,
            'status'  => 'pending',
        ]);

        $registration->players()->attach($playerIds);

        return [
            'message'      => 'Demande d\'inscription soumise. En attente de validation par l\'organisateur.',
            'registration' => $registration->load('players'),
        ];
    }

    public function getByCompetition(string $competitionId): array
    {
        $competition = Competition::find($competitionId);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        if ($competition->organizer_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        $registrations = Registration::with(['team.players'])
            ->where('competition_id', $competitionId)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'status'     => $r->status,
                'created_at' => $r->created_at,
                'team'       => [
                    'id'            => $r->team->id,
                    'name'          => $r->team->name,
                    'logo'          => $r->team->logo,
                    'players_count' => $r->team->players->count(),
                ],
            ]);

        return [
            'registrations' => $registrations,
            'meta' => [
                'total'    => $registrations->count(),
                'pending'  => $registrations->where('status', 'pending')->count(),
                'approved' => $registrations->where('status', 'approved')->count(),
                'rejected' => $registrations->where('status', 'rejected')->count(),
            ],
        ];
    }

    public function approve(string $registrationId): array
    {
        $registration = Registration::with('competition')->find($registrationId);

        if (!$registration) {
            throw new \Exception('Inscription introuvable.', 404);
        }

        if ($registration->competition->organizer_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        if ($registration->status !== 'pending') {
            throw new \Exception('Seules les inscriptions en attente peuvent être approuvées.', 400);
        }

        $registration->update(['status' => 'approved']);

        broadcast(new TeamValidated($registration->fresh()))->toOthers();

        $this->checkCompetitionFull($registration->competition);

        return ['message' => 'Inscription approuvée avec succès.', 'registration' => $registration->fresh()];
    }

    public function reject(string $registrationId): array
    {
        $registration = Registration::with('competition')->find($registrationId);

        if (!$registration) {
            throw new \Exception('Inscription introuvable.', 404);
        }

        if ($registration->competition->organizer_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        if ($registration->status !== 'pending') {
            throw new \Exception('Seules les inscriptions en attente peuvent être refusées.', 400);
        }

        $registration->update(['status' => 'rejected']);

        broadcast(new TeamRejected($registration->fresh()))->toOthers();

        return ['message' => 'Inscription refusée.', 'registration' => $registration->fresh()];
    }

    private function checkCompetitionFull(Competition $competition): void
    {
        $competition->refresh();

        $approvedCount = $competition->registrations()
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= $competition->max_teams) {
            $groupsExist = Group::where('competition_id', $competition->id)->exists();

            if (!$groupsExist) {
                $competition->update(['status' => 'full']);
                event(new CompetitionFull($competition->fresh()));
            }
        }
    }
}
