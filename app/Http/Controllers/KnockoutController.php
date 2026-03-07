<?php

namespace App\Http\Controllers;

use App\Services\KnockoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Knockout", description="Gestion des phases éliminatoires")
 */
class KnockoutController extends Controller
{
    public function __construct(
        protected KnockoutService $knockoutService
    ) {}

    /**
     * @OA\Post(
     *     path="/competitions/{id}/generate-knockout",
     *     summary="Générer les phases éliminatoires d'une compétition",
     *     description="Génère le premier tour des phases éliminatoires à partir du classement des groupes. Tous les matchs de groupes doivent être clôturés.",
     *     tags={"Knockout"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phases éliminatoires générées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Phases éliminatoires générées avec succès."),
     *             @OA\Property(property="round", type="string", enum={"semi","final"}, example="semi"),
     *             @OA\Property(property="matches", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="round_type", type="string", example="semi"),
     *                     @OA\Property(property="status", type="string", example="scheduled"),
     *                     @OA\Property(property="week_number", type="integer", example=4),
     *                     @OA\Property(property="day_of_week", type="integer", example=6, description="0=Dimanche, 6=Samedi"),
     *                     @OA\Property(property="match_time", type="string", example="10:00:00"),
     *                     @OA\Property(property="group_id", type="string", nullable=true, example=null),
     *                     @OA\Property(property="home_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 8"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     ),
     *                     @OA\Property(property="away_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 3"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Tous les matchs de groupes ne sont pas encore joués",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tous les matchs de la phase de groupes doivent être joués.")
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
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas l'organisateur de cette compétition.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Compétition introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compétition introuvable.")
     *         )
     *     )
     * )
     */
    public function generateKnockout(string $id): JsonResponse
    {
        try {
            $result = $this->knockoutService->generateKnockout($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/competitions/{id}/next-round",
     *     summary="Générer le tour suivant des phases éliminatoires",
     *     description="Génère le tour suivant à partir des gagnants du tour précédent. Tous les matchs du tour actuel doivent être clôturés. Les matchs nuls doivent avoir des tirs au but enregistrés.",
     *     tags={"Knockout"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tour suivant généré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Finale générée avec succès."),
     *             @OA\Property(property="round", type="string", enum={"semi","final"}, example="final"),
     *             @OA\Property(property="matches", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="round_type", type="string", example="final"),
     *                     @OA\Property(property="status", type="string", example="scheduled"),
     *                     @OA\Property(property="week_number", type="integer", example=5),
     *                     @OA\Property(property="day_of_week", type="integer", example=6, description="0=Dimanche, 6=Samedi"),
     *                     @OA\Property(property="match_time", type="string", example="10:00:00"),
     *                     @OA\Property(property="group_id", type="string", nullable=true, example=null),
     *                     @OA\Property(property="home_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 8"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     ),
     *                     @OA\Property(property="away_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 5"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Match nul sans tirs au but / Tour déjà généré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Le match entre Les Lions 8 et Les Lions 3 est nul. Saisissez les tirs au but.")
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
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas l'organisateur de cette compétition.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Compétition introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compétition introuvable.")
     *         )
     *     )
     * )
     */
    public function generateNextRound(string $id): JsonResponse
    {
        try {
            $result = $this->knockoutService->generateNextRound($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/matches/{id}/penalties",
     *     summary="Saisir les tirs au but d'un match éliminatoire nul",
     *     description="Enregistre les tirs au but pour un match éliminatoire. Uniquement disponible si le match est nul après le temps réglementaire.",
     *     tags={"Knockout"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du match",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"home_penalty","away_penalty"},
     *             @OA\Property(property="home_penalty", type="integer", minimum=0, example=4, description="Nombre de tirs au but réussis par l'équipe domicile"),
     *             @OA\Property(property="away_penalty", type="integer", minimum=0, example=3, description="Nombre de tirs au but réussis par l'équipe extérieure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tirs au but enregistrés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tirs au but enregistrés."),
     *             @OA\Property(property="home_penalty", type="integer", example=4),
     *             @OA\Property(property="away_penalty", type="integer", example=3),
     *             @OA\Property(property="winner", type="string", example="Les Lions 8")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Match non éliminatoire ou non nul",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les tirs au but ne sont disponibles que pour les matchs éliminatoires nuls.")
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
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas l'organisateur de cette compétition.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Match introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Match introuvable.")
     *         )
     *     )
     * )
     */
    public function addPenalties(Request $request, string $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'home_penalty' => 'required|integer|min:0',
                'away_penalty' => 'required|integer|min:0',
            ]);

            $result = $this->knockoutService->addPenalties($id, $data);
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