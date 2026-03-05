<?php

namespace App\Http\Controllers;

use App\Http\Requests\Competitions\StoreCompetitionRequest;
use App\Http\Requests\Competitions\UpdateCompetitionRequest;
use App\Services\CompetitionService;
use Illuminate\Http\JsonResponse;

class CompetitionController extends Controller
{
    public function __construct(
        protected CompetitionService $competitionService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/competitions",
     *     summary="Lister toutes les compétitions publiques",
     *     tags={"Compétitions"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des compétitions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competitions", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                         @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                         @OA\Property(property="status", type="string", example="registration_open"),
     *                         @OA\Property(property="start_date", type="string", example="2026-03-15"),
     *                         @OA\Property(property="end_date", type="string", example="2026-03-25")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $result = $this->competitionService->getPublicCompetitions();
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/competitions/my",
     *     summary="Lister les compétitions de l'organisateur connecté",
     *     tags={"Compétitions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mes compétitions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competitions", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                     @OA\Property(property="status", type="string", example="registration_open")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function myCompetitions(): JsonResponse
    {
        try {
            $result = $this->competitionService->getMyCompetitions();
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/competitions/{id}",
     *     summary="Afficher le détail d'une compétition",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail de la compétition",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                 @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                 @OA\Property(property="status", type="string", example="registration_open"),
     *                 @OA\Property(property="start_date", type="string", example="2026-03-15"),
     *                 @OA\Property(property="end_date", type="string", example="2026-03-25"),
     *                 @OA\Property(property="max_teams", type="integer", example=8),
     *                 @OA\Property(property="players_per_team", type="integer", example=11),
     *                 @OA\Property(property="registration_fee", type="number", example=5000)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Compétition introuvable")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $result = $this->competitionService->getById($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/competitions",
     *     summary="Créer une nouvelle compétition",
     *     tags={"Compétitions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","location","start_date","end_date","max_teams","players_per_team"},
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                 @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2026-03-15"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2026-03-25"),
     *                 @OA\Property(property="max_teams", type="integer", example=8),
     *                 @OA\Property(property="players_per_team", type="integer", example=11),
     *                 @OA\Property(property="registration_fee", type="number", example=5000),
     *                 @OA\Property(property="prize_description", type="string", example="Trophée + 500 000 FCFA"),
     *                 @OA\Property(property="age_min", type="integer", example=18),
     *                 @OA\Property(property="age_max", type="integer", example=40),
     *                 @OA\Property(property="poster_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compétition créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                 @OA\Property(property="status", type="string", example="registration_open")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(StoreCompetitionRequest $request): JsonResponse
    {
        try {
            $result = $this->competitionService->create($request->validated());
            return response()->json(['success' => true, ...$result], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/competitions/{id}",
     *     summary="Modifier une compétition",
     *     tags={"Compétitions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana 2026"),
     *                 @OA\Property(property="location", type="string", example="Bobo-Dioulasso"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2026-03-15"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2026-03-25"),
     *                 @OA\Property(property="max_teams", type="integer", example=16),
     *                 @OA\Property(property="players_per_team", type="integer", example=11),
     *                 @OA\Property(property="registration_fee", type="number", example=5000),
     *                 @OA\Property(property="prize_description", type="string", example="Trophée + 500 000 FCFA"),
     *                 @OA\Property(property="age_min", type="integer", example=18),
     *                 @OA\Property(property="age_max", type="integer", example=40),
     *                 @OA\Property(property="poster_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compétition modifiée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana 2026")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Compétition introuvable")
     * )
     */
    public function update(UpdateCompetitionRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->competitionService->update($id, $request->validated());
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/competitions/{id}",
     *     summary="Supprimer une compétition",
     *     tags={"Compétitions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compétition supprimée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compétition supprimée avec succès.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Compétition introuvable")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $result = $this->competitionService->delete($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/competitions/{id}/groups",
     *     summary="Groupes d'une compétition",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Groupes de la compétition"),
     *     @OA\Response(response=404, description="Compétition introuvable")
     * )
     */
    public function groups(string $id): JsonResponse
    {
        try {
            $competition = \App\Models\Competition::with([
                'groups.teams.players',
            ])->find($id);

            if (!$competition) {
                throw new \Exception('Compétition introuvable.', 404);
            }

            return response()->json([
                'success' => true,
                'competition' => $competition->name,
                'groups' => $competition->groups,
            ], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/competitions/{id}/matches",
     *     summary="Matchs d'une compétition",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Matchs de la compétition"),
     *     @OA\Response(response=404, description="Compétition introuvable")
     * )
     */
    public function matches(string $id): JsonResponse
    {
        try {
            $competition = \App\Models\Competition::with([
                'matches.homeTeam',
                'matches.awayTeam',
                'matches.group',
            ])->find($id);

            if (!$competition) {
                throw new \Exception('Compétition introuvable.', 404);
            }

            // Grouper les matchs par semaine
            $matchesByWeek = $competition->matches
                ->groupBy('week_number')
                ->map(function ($weekMatches, $weekNumber) {
                    return [
                        'week' => 'Semaine ' . $weekNumber,
                        'matches' => $weekMatches->groupBy('day_of_week'),
                    ];
                });

            return response()->json([
                'success' => true,
                'competition' => $competition->name,
                'programme' => $matchesByWeek,
            ], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleException(\Exception $e): JsonResponse
    {
        $code = in_array($e->getCode(), [400, 401, 403, 404, 422]) ? $e->getCode() : 500;
        return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
    }
}