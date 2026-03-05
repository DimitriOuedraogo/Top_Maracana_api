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
}