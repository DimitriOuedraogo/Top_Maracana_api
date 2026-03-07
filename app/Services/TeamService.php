<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Team;
use App\Repositories\TeamRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Events\CompetitionFull;

class TeamService
{
    public function __construct(
        protected TeamRepository $teamRepository
    ) {
    }

    // ── Lister toutes les équipes ─────────────────────────────
    public function getAll(): array
    {
        $teams = $this->teamRepository->getAll();
        return ['teams' => $teams];
    }

    // ── Mes équipes (manager connecté) ────────────────────────
    public function getMyTeams(): array
    {
        $teams = $this->teamRepository->findByManager(Auth::id());

        \Log::info('Auth ID: ' . Auth::id());
        \Log::info('Teams: ' . $teams);

        return ['teams' => $teams];
    }

    // ── Détail d'une équipe ───────────────────────────────────
    public function getById(string $id): array
    {
        $team = $this->teamRepository->findById($id);

        if (!$team) {
            throw new \Exception('Équipe introuvable.', 404);
        }

        return ['team' => $team];
    }

    // ── Créer une équipe + inscription automatique ────────────
    public function create(array $data): array
    {
        // 1. Récupérer la compétition
        $competition = Competition::find($data['competition_id']);

        if (!$competition) {
            throw new \Exception('Compétition introuvable.', 404);
        }

        // 2. Vérifier que la compétition est ouverte
        if ($competition->status !== 'registration_open') {
            throw new \Exception('Les inscriptions sont fermées pour cette compétition.', 400);
        }

        // 3. Vérifier que max_teams n'est pas atteint
        $approvedCount = $competition->registrations()
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= $competition->max_teams) {
            throw new \Exception('Le nombre maximum d\'équipes est atteint.', 400);
        }

        // // 4. Vérifier que le manager n'a pas déjà une équipe dans cette compétition
        // $existing = $this->teamRepository->findByManagerAndCompetition(
        //     Auth::id(),
        //     $data['competition_id']
        // );

        // if ($existing) {
        //     throw new \Exception('Vous avez déjà une équipe dans cette compétition.', 400);
        // }

        // 5. Vérifier le nombre de joueurs
        $players = $data['players'] ?? [];

        if (count($players) !== $competition->players_per_team) {
            throw new \Exception(
                "Vous devez avoir exactement {$competition->players_per_team} joueurs.",
                400
            );
        }


        // 5.1 Vérifier qu'il y a exactement 1 gardien ← NOUVEAU
        $goalkeeperCount = collect($players)->where('is_goalkeeper', true)->count();

        if ($goalkeeperCount === 0) {
            throw new \Exception('Vous devez avoir au moins 1 gardien de but.', 400);
        }

        if ($goalkeeperCount > 1) {
            throw new \Exception('Vous ne pouvez avoir qu\'un seul gardien de but.', 400);
        }

        // 6. Vérifier la tranche d'âge des joueurs
        foreach ($players as $player) {
            $age = Carbon::parse($player['birth_date'])->age;

            if ($competition->age_min && $age < $competition->age_min) {
                throw new \Exception(
                    "Le joueur {$player['full_name']} est trop jeune (âge minimum: {$competition->age_min} ans).",
                    400
                );
            }

            if ($competition->age_max && $age > $competition->age_max) {
                throw new \Exception(
                    "Le joueur {$player['full_name']} est trop âgé (âge maximum: {$competition->age_max} ans).",
                    400
                );
            }
        }



        // 7. Créer l'équipe
        $data['manager_id'] = Auth::id();

        if (isset($data['logo'])) {
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        $team = $this->teamRepository->create($data);

        // 8. Ajouter les joueurs
        foreach ($players as $player) {
            $team->players()->create($player);
        }

        // 9. Créer l'inscription automatiquement
        $competition->registrations()->create([
            'team_id' => $team->id,
            'status' => 'approved',
        ]);

        // 10. Vérifier si max_teams est atteint
        $this->checkCompetitionFull($competition);

        return ['team' => $team->load('players')];
    }

    // ── Modifier une équipe ───────────────────────────────────
    public function update(string $id, array $data): array
    {
        $team = $this->teamRepository->findById($id);

        if (!$team) {
            throw new \Exception('Équipe introuvable.', 404);
        }

        if ($team->manager_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        // Vérifier la tranche d'âge si les joueurs sont modifiés
        if (!empty($data['players'])) {
            $competition = $team->competition;

            foreach ($data['players'] as $player) {
                $age = Carbon::parse($player['birth_date'])->age;

                if ($competition->age_min && $age < $competition->age_min) {
                    throw new \Exception(
                        "Le joueur {$player['full_name']} est trop jeune (âge minimum: {$competition->age_min} ans).",
                        400
                    );
                }

                if ($competition->age_max && $age > $competition->age_max) {
                    throw new \Exception(
                        "Le joueur {$player['full_name']} est trop âgé (âge maximum: {$competition->age_max} ans).",
                        400
                    );
                }
            }

            $team->players()->delete();
            foreach ($data['players'] as $player) {
                $team->players()->create($player);
            }
        }

        if (isset($data['logo'])) {
            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        $team = $this->teamRepository->update($team, $data);
        return ['team' => $team];
    }

    // ── Supprimer une équipe ──────────────────────────────────
    public function delete(string $id): array
    {
        $team = $this->teamRepository->findById($id);

        if (!$team) {
            throw new \Exception('Équipe introuvable.', 404);
        }

        if ($team->manager_id !== Auth::id()) {
            throw new \Exception('Action non autorisée.', 403);
        }

        if ($team->logo) {
            Storage::disk('public')->delete($team->logo);
        }

        $this->teamRepository->delete($team);
        return ['message' => 'Équipe supprimée avec succès.'];
    }

    // ── Vérifier si la compétition est pleine ─────────────────
    private function checkCompetitionFull(Competition $competition): void
    {
        // Recharger depuis la base pour avoir les données fraîches
        $competition->refresh();

        $approvedCount = $competition->registrations()
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= $competition->max_teams) {

            // Vérifier directement en base sans cache
            $groupsExist = \App\Models\Group::where('competition_id', $competition->id)->exists();

            if (!$groupsExist) {
                $competition->update(['status' => 'full']);
                event(new CompetitionFull($competition->fresh()));
            }
        }
    }

    // ── Upload logo ───────────────────────────────────────────
    private function uploadLogo($file): string
    {
        return $file->store('teams/logos', 'public');
    }
}