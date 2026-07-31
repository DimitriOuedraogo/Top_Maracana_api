<?php

namespace App\Services;

use App\Models\Team;
use App\Repositories\TeamRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $formatted = $teams->map(function ($team) {
            return [
                'id'             => $team->id,
                'name'           => $team->name,
                'logo'           => $team->logo,
                'players_count'  => $team->players->count(),
                'players'        => $team->players,
                'registrations'  => $team->registrations->map(fn ($reg) => [
                    'id'           => $reg->id,
                    'status'       => $reg->status,
                    'created_at'   => $reg->created_at,
                    'competition'  => [
                        'id'     => $reg->competition->id,
                        'name'   => $reg->competition->name,
                        'status' => $reg->competition->status,
                        'players_per_team' => $reg->competition->players_per_team,
                    ],
                    'players_count' => $reg->players->count(),
                    'players'       => $reg->players,
                ]),
            ];
        });

        return ['teams' => $formatted];
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

    // ── Créer une équipe (indépendante de toute compétition) ──
    public function create(array $data): array
    {
        $players = $data['players'] ?? [];

        $goalkeeperCount = collect($players)->where('is_goalkeeper', true)->count();
        if ($goalkeeperCount === 0) {
            throw new \Exception('Vous devez avoir au moins 1 gardien de but.', 400);
        }
        if ($goalkeeperCount > 1) {
            throw new \Exception('Vous ne pouvez avoir qu\'un seul gardien de but.', 400);
        }

        $data['manager_id'] = Auth::id();

        if (isset($data['logo'])) {
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        $team = $this->teamRepository->create($data);

        foreach ($players as $player) {
            $team->players()->create($player);
        }

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

        if (!empty($data['players'])) {
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

    // ── Upload logo ───────────────────────────────────────────
    private function uploadLogo($file): string
    {
        return $file->store('teams/logos', 'public');
    }
}