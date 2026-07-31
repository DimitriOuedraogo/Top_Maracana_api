<?php

namespace App\Http\Controllers;

use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Inscriptions", description="Gestion des inscriptions aux compétitions")
 */
class RegistrationController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/competitions/{id}/register",
     *     summary="Inscrire une équipe existante à une compétition",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"team_id"},
     *             @OA\Property(property="team_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Inscription soumise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Demande d'inscription soumise. En attente de validation par l'organisateur."),
     *             @OA\Property(property="registration", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="team_id", type="string", format="uuid"),
     *                 @OA\Property(property="competition_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation métier échouée (inscriptions fermées, trop/pas assez de joueurs, âge, etc.)"),
     *     @OA\Response(response=403, description="L'équipe n'appartient pas au manager connecté"),
     *     @OA\Response(response=404, description="Compétition ou équipe introuvable")
     * )
     */
    public function register(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'team_id'      => 'required|uuid|exists:teams,id',
            'player_ids'   => 'required|array',
            'player_ids.*' => 'uuid|exists:players,id',
        ]);

        try {
            $result = $this->registrationService->register(
                $id,
                $request->input('team_id'),
                $request->input('player_ids')
            );
            return response()->json(['success' => true, ...$result], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/competitions/{id}/registrations",
     *     summary="Lister les inscriptions d'une compétition (organisateur uniquement)",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des inscriptions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="registrations", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=10),
     *                 @OA\Property(property="pending", type="integer", example=3),
     *                 @OA\Property(property="approved", type="integer", example=6),
     *                 @OA\Property(property="rejected", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Compétition introuvable")
     * )
     */
    public function index(string $id): JsonResponse
    {
        try {
            $result = $this->registrationService->getByCompetition($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Patch(
     *     path="/registrations/{id}/approve",
     *     summary="Approuver une inscription",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription approuvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inscription approuvée avec succès.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Statut invalide"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Inscription introuvable")
     * )
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $result = $this->registrationService->approve($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Patch(
     *     path="/registrations/{id}/reject",
     *     summary="Refuser une inscription",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription refusée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inscription refusée.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Statut invalide"),
     *     @OA\Response(response=403, description="Action non autorisée"),
     *     @OA\Response(response=404, description="Inscription introuvable")
     * )
     */
    public function reject(string $id): JsonResponse
    {
        try {
            $result = $this->registrationService->reject($id);
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
