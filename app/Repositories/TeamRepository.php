<?php

namespace App\Repositories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class TeamRepository
{
    public function getAll(): Collection
    {
        return Team::with(['manager', 'players'])->latest()->get();
    }

    public function findById(string $id): ?Team
    {
        return Team::with(['manager', 'players', 'competition'])->find($id);
    }

    public function findByManager(string $managerId): Collection
    {
        return Team::with(['players', 'competition'])
                   ->where('manager_id', $managerId)
                   ->get();
    }

    public function findByManagerAndCompetition(string $managerId, string $competitionId): ?Team
    {
        return Team::with(['players'])
                   ->where('manager_id', $managerId)
                   ->where('competition_id', $competitionId)
                   ->first();
    }

    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh(['manager', 'players']);
    }

    public function delete(Team $team): bool
    {
        return $team->delete();
    }
}