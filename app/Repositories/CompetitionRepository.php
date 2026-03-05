<?php

namespace App\Repositories;

use App\Models\Competition;
use Illuminate\Pagination\LengthAwarePaginator;

class CompetitionRepository
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Competition::with(['organizer', 'days', 'timeSlots'])
            ->latest()
            ->paginate($perPage);
    }

    public function getPublic(int $perPage = 15): LengthAwarePaginator
    {
        return Competition::with(['organizer', 'days', 'timeSlots'])
            ->whereIn('status', ['registration_open', 'ongoing', 'finished'])
            ->latest()
            ->paginate($perPage);
    }

    public function findById(string $id): ?Competition
    {
        $competition = Competition::with([
            'organizer',
            'days',
            'timeSlots',
            'groups.teams.players', // ← groupes + équipes + joueurs
            'matches.homeTeam',     // ← matchs + équipe domicile
            'matches.awayTeam',     // ← matchs + équipe extérieur
            'matches.group',        // ← groupe du match
        ])->find($id);

        
        return $competition;
    }

    public function create(array $data): Competition
    {
        return Competition::create($data);
    }

    public function update(Competition $competition, array $data): Competition
    {
        $competition->update($data);
        return $competition->fresh(['organizer', 'days', 'timeSlots']);
    }

    public function delete(Competition $competition): bool
    {
        return $competition->delete();
    }

    public function getByOrganizer(string $organizerId): \Illuminate\Database\Eloquent\Collection
    {
        return Competition::with(['days', 'timeSlots'])
            ->where('organizer_id', $organizerId)
            ->latest()
            ->get();
    }
}