<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Matches\AddCardRequest;
use App\Http\Requests\Matches\AddGoalRequest;
use App\Services\MatchService;

class MatchController extends Controller
{
    public function __construct(
        protected MatchService $matchService
    ) {
    }
    /**
     * @OA\Get(
     *     path="/api/matches",
     *     summary="Lister tous les matchs",
     *     tags={"Matchs"},
     *     @OA\Response(response=200, description="Liste des matchs")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $matches = GameMatch::with([
                'competition',
                'group',
                'homeTeam',
                'awayTeam',
            ])->orderBy('week_number')->orderBy('day_of_week')->get();

            return response()->json(['success' => true, 'matches' => $matches], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/matches/{id}/goals",
     *     summary="Ajouter un but",
     *     tags={"Matchs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_id"},
     *             @OA\Property(property="player_id", type="string", example="uuid-du-joueur")
     *         )
     *     ),
     *     @OA\Response(response=200, description="But ajouté avec succès"),
     *     @OA\Response(response=400, description="Erreur"),
     *     @OA\Response(response=404, description="Match ou joueur introuvable")
     * )
     */
    public function addGoal(AddGoalRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->matchService->addGoal($id, $request->validated());
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/matches/{id}/cards",
     *     summary="Ajouter un carton",
     *     tags={"Matchs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_id","card_type","minute"},
     *             @OA\Property(property="player_id", type="string", example="uuid-du-joueur"),
     *             @OA\Property(property="card_type", type="string", enum={"yellow","red"}),
     *             @OA\Property(property="minute", type="integer", example=45)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Carton ajouté avec succès"),
     *     @OA\Response(response=400, description="Erreur"),
     *     @OA\Response(response=404, description="Match ou joueur introuvable")
     * )
     */
    public function addCard(AddCardRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->matchService->addCard($id, $request->validated());
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/matches/{id}/close",
     *     summary="Clôturer un match",
     *     tags={"Matchs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Match clôturé avec succès"),
     *     @OA\Response(response=400, description="Match déjà clôturé"),
     *     @OA\Response(response=404, description="Match introuvable")
     * )
     */
    public function closeMatch(string $id): JsonResponse
    {
        try {
            $result = $this->matchService->closeMatch($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/matches/{id}",
     *     summary="Détail d'un match",
     *     tags={"Matchs"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Détail du match"),
     *     @OA\Response(response=404, description="Match introuvable")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $match = GameMatch::with([
                'competition',
                'group',
                'homeTeam',
                'awayTeam',
            ])->find($id);

            if (!$match) {
                throw new \Exception('Match introuvable.', 404);
            }

            return response()->json(['success' => true, 'match' => $match], 200);
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