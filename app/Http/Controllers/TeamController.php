<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Équipes", description="Gestion des équipes")
 */
class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teamService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/teams",
     *     summary="Lister toutes les équipes",
     *     tags={"Équipes"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de toutes les équipes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="teams", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                     @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="manager_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                     @OA\Property(property="logo", type="string", nullable=true, example=null, description="Chemin vers le logo de l'équipe"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-03-07T13:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-03-07T13:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $result = $this->teamService->getAll();
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/teams/my",
     *     summary="Lister les équipes du manager connecté",
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mes équipes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="teams", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                     @OA\Property(property="logo", type="string", nullable=true, example=null),
     *                     @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="competition", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                         @OA\Property(property="status", type="string", enum={"registration_open","full","ongoing","finished"}, example="ongoing")
     *                     ),
     *                     @OA\Property(property="players", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                             @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                             @OA\Property(property="birth_date", type="string", format="date", example="2000-01-01"),
     *                             @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                             @OA\Property(property="national_id_number", type="string", nullable=true, example=null),
     *                             @OA\Property(property="national_id_photo", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié.")
     *         )
     *     )
     * )
     */
    public function myTeams(): JsonResponse
    {
        try {
            $result = $this->teamService->getMyTeams();
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/teams/{id}",
     *     summary="Détail d'une équipe avec ses joueurs",
     *     tags={"Équipes"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de l'équipe",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail de l'équipe",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="team", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                 @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="manager_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                 @OA\Property(property="logo", type="string", nullable=true, example=null),
     *                 @OA\Property(property="players", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                         @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                         @OA\Property(property="birth_date", type="string", format="date", example="2000-01-01"),
     *                         @OA\Property(property="is_goalkeeper", type="boolean", example=false, description="True si le joueur est gardien de but"),
     *                         @OA\Property(property="national_id_number", type="string", nullable=true, example=null),
     *                         @OA\Property(property="national_id_photo", type="string", nullable=true, example=null, description="Chemin vers la photo de la CNI")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Équipe introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Équipe introuvable.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $result = $this->teamService->getById($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/teams",
     *     summary="Créer une équipe et s'inscrire à une compétition",
     *     description="Crée une équipe avec ses joueurs et l'inscrit automatiquement à la compétition. L'équipe doit avoir exactement 1 gardien de but.",
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"competition_id","name","players"},
     *             @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="name", type="string", example="Les Lions 1"),
     *             @OA\Property(
     *                 property="players",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"full_name","birth_date","is_goalkeeper"},
     *                     @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="2000-01-01"),
     *                     @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                     @OA\Property(property="national_id_number", type="string", nullable=true, example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Équipe créée et inscrite avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur métier"),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        try {
            $result = $this->teamService->create($request->validated());
            return response()->json(['success' => true, ...$result], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    /**
     * @OA\Post(
     *     path="/teams/{id}",
     *     summary="Modifier une équipe",
     *     description="Modifie les informations d'une équipe. Seul le manager de l'équipe peut la modifier.",
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de l'équipe",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", nullable=true, example="Les Lions FC"),
     *             @OA\Property(
     *                 property="players",
     *                 type="array",
     *                 nullable=true,
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="full_name", type="string", example="Nouveau Joueur"),
     *                     @OA\Property(property="birth_date", type="string", format="date", example="2000-01-01"),
     *                     @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                     @OA\Property(property="national_id_number", type="string", nullable=true, example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Équipe modifiée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Équipe introuvable")
     * )
     */
    public function update(UpdateTeamRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->teamService->update($id, $request->validated());
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/teams/{id}",
     *     summary="Supprimer une équipe",
     *     description="Supprime une équipe et ses joueurs. Seul le manager de l'équipe peut la supprimer.",
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de l'équipe",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Équipe supprimée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Équipe supprimée avec succès.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Action non autorisée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas autorisé à supprimer cette équipe.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Équipe introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Équipe introuvable.")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $result = $this->teamService->delete($id);
            return response()->json(['success' => true, ...$result], 200);
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