<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teams\StoreTeamRequest;
use App\Http\Requests\Teams\UpdateTeamRequest;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teamService
    ) {}

    /**
     * @OA\Get(
     *     path="/teams",
     *     summary="Lister toutes les équipes",
     *     tags={"Équipes"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des équipes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="teams", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Les Lions"),
     *                     @OA\Property(property="logo", type="string", example="teams/logos/logo.png")
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
     *     summary="Mes équipes",
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mes équipes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="teams", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Les Lions"),
     *                     @OA\Property(property="competition", type="object",
     *                         @OA\Property(property="name", type="string", example="Tournoi Maracana")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function myTeams(): JsonResponse
    {
        try {
            $result = $this->teamService->getMyTeams();
                \Log::info('Resultats: ' . json_encode($result));
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/teams/{id}",
     *     summary="Détail d'une équipe",
     *     tags={"Équipes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de l'équipe",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail de l'équipe",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="team", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Les Lions"),
     *                 @OA\Property(property="players", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="full_name", type="string", example="Dimitri Ouedraogo"),
     *                         @OA\Property(property="birth_date", type="string", example="2000-01-01")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Équipe introuvable")
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
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"competition_id","name","players"},
     *             @OA\Property(property="competition_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="name", type="string", example="Les Lions"),
     *             @OA\Property(property="players", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="full_name", type="string", example="Dimitri Ouedraogo"),
     *                     @OA\Property(property="birth_date", type="string", example="2000-01-01"),
     *                     @OA\Property(property="national_id_number", type="string", example="BF123456")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Équipe créée et inscrite avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="team", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Les Lions")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur de validation métier"),
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
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de l'équipe",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Les Lions FC"),
     *             @OA\Property(property="players", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="full_name", type="string", example="Dimitri Ouedraogo"),
     *                     @OA\Property(property="birth_date", type="string", example="2000-01-01")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Équipe modifiée avec succès"),
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
     *     tags={"Équipes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de l'équipe",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(response=200, description="Équipe supprimée avec succès"),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Équipe introuvable")
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