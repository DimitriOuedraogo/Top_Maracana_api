<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Matches\AddCardRequest;
use App\Http\Requests\Matches\AddGoalRequest;
use App\Services\MatchService;

/**
 * @OA\Tag(name="Matchs", description="Gestion des matchs")
 */
class MatchController extends Controller
{
    public function __construct(
        protected MatchService $matchService
    ) {}

    /**
     * @OA\Get(
     *     path="/matches",
     *     summary="Lister tous les matchs",
     *     tags={"Matchs"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de tous les matchs",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="matches", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="group_id", type="string", format="uuid", nullable=true, example=null, description="Null pour les matchs éliminatoires"),
     *                     @OA\Property(property="round_type", type="string", enum={"group","semi","final"}, example="group"),
     *                     @OA\Property(property="status", type="string", enum={"scheduled","played"}, example="scheduled"),
     *                     @OA\Property(property="week_number", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="day_of_week", type="integer", nullable=true, example=6, description="0=Dimanche, 6=Samedi"),
     *                     @OA\Property(property="match_time", type="string", nullable=true, example="10:00:00"),
     *                     @OA\Property(property="competition", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                         @OA\Property(property="status", type="string", example="ongoing")
     *                     ),
     *                     @OA\Property(property="group", type="object", nullable=true,
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                         @OA\Property(property="name", type="string", example="Groupe A")
     *                     ),
     *                     @OA\Property(property="home_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     ),
     *                     @OA\Property(property="away_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440004"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 2"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
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
     * @OA\Get(
     *     path="/matches/{id}",
     *     summary="Détail d'un match avec buts et cartons",
     *     tags={"Matchs"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du match",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail du match",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="match", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="group_id", type="string", format="uuid", nullable=true, example=null, description="Null pour les matchs éliminatoires"),
     *                 @OA\Property(property="round_type", type="string", enum={"group","semi","final"}, example="final"),
     *                 @OA\Property(property="status", type="string", enum={"scheduled","played"}, example="played"),
     *                 @OA\Property(property="week_number", type="integer", nullable=true, example=5),
     *                 @OA\Property(property="day_of_week", type="integer", nullable=true, example=6, description="0=Dimanche, 6=Samedi"),
     *                 @OA\Property(property="match_time", type="string", nullable=true, example="10:00:00"),
     *                 @OA\Property(property="competition", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                     @OA\Property(property="status", type="string", example="finished"),
     *                     @OA\Property(property="winner_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440002"),
     *                     @OA\Property(property="top_scorer_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440003"),
     *                     @OA\Property(property="best_player_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440004"),
     *                     @OA\Property(property="best_goalkeeper_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440005")
     *                 ),
     *                 @OA\Property(property="group", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example=null),
     *                     @OA\Property(property="name", type="string", example=null)
     *                 ),
     *                 @OA\Property(property="home_team", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                     @OA\Property(property="name", type="string", example="Les Lions 8"),
     *                     @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                 ),
     *                 @OA\Property(property="away_team", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                     @OA\Property(property="name", type="string", example="Les Lions 2"),
     *                     @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                 ),
     *                 @OA\Property(property="result", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440006"),
     *                     @OA\Property(property="home_score", type="integer", example=2),
     *                     @OA\Property(property="away_score", type="integer", example=1),
     *                     @OA\Property(property="home_penalty_score", type="integer", nullable=true, example=null, description="Null si pas de tirs au but"),
     *                     @OA\Property(property="away_penalty_score", type="integer", nullable=true, example=null, description="Null si pas de tirs au but")
     *                 ),
     *                 @OA\Property(property="goals", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440007"),
     *                         @OA\Property(property="minute", type="integer", example=23),
     *                         @OA\Property(property="player", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440008"),
     *                             @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                             @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                             @OA\Property(property="national_id_number", type="string", nullable=true, example=null),
     *                             @OA\Property(property="national_id_photo", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="cards", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440009"),
     *                         @OA\Property(property="card_type", type="string", enum={"yellow","red"}, example="yellow"),
     *                         @OA\Property(property="minute", type="integer", example=60),
     *                         @OA\Property(property="player", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440010"),
     *                             @OA\Property(property="full_name", type="string", example="Joueur 2"),
     *                             @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                             @OA\Property(property="national_id_number", type="string", nullable=true, example=null),
     *                             @OA\Property(property="national_id_photo", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 )
     *             )
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
    public function show(string $id): JsonResponse
    {
        try {
            $match = GameMatch::with([
                'competition',
                'group',
                'homeTeam',
                'awayTeam',
                'result',
                'goals.player',
                'cards.player',
            ])->find($id);

            if (!$match) {
                throw new \Exception('Match introuvable.', 404);
            }

            return response()->json(['success' => true, 'match' => $match], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/matches/{id}/goals",
     *     summary="Ajouter un but à un match",
     *     description="Enregistre un but pour un joueur. Seul l'organisateur peut saisir les buts. Le match doit être en cours (non clôturé).",
     *     tags={"Matchs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du match",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_id","minute"},
     *             @OA\Property(property="player_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001", description="UUID du joueur qui a marqué"),
     *             @OA\Property(property="minute", type="integer", minimum=1, maximum=120, example=23, description="Minute du but (1-120)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="But ajouté avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="But ajouté avec succès."),
     *             @OA\Property(property="scorer", type="string", example="Joueur 1"),
     *             @OA\Property(property="minute", type="integer", example=23),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="home_score", type="integer", example=1),
     *                 @OA\Property(property="away_score", type="integer", example=0),
     *                 @OA\Property(property="home_penalty_score", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="away_penalty_score", type="integer", nullable=true, example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Match déjà clôturé ou joueur non membre du match",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ce match est déjà clôturé.")
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
     *     @OA\Response(response=404, description="Match ou joueur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joueur introuvable.")
     *         )
     *     )
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
     *     path="/matches/{id}/cards",
     *     summary="Ajouter un carton à un joueur",
     *     description="Enregistre un carton jaune ou rouge pour un joueur. Seul l'organisateur peut saisir les cartons. Un joueur ne peut pas recevoir deux cartons rouges.",
     *     tags={"Matchs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du match",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_id","card_type","minute"},
     *             @OA\Property(property="player_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001", description="UUID du joueur qui reçoit le carton"),
     *             @OA\Property(property="card_type", type="string", enum={"yellow","red"}, example="yellow", description="Type de carton : yellow ou red"),
     *             @OA\Property(property="minute", type="integer", minimum=1, maximum=120, example=45, description="Minute du carton (1-120)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carton ajouté avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carton ajouté avec succès."),
     *             @OA\Property(property="player", type="string", example="Joueur 1"),
     *             @OA\Property(property="card_type", type="string", example="yellow"),
     *             @OA\Property(property="minute", type="integer", example=45)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Match clôturé ou double carton rouge",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ce joueur a déjà reçu un carton rouge dans ce match.")
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
     *     @OA\Response(response=404, description="Match ou joueur introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joueur introuvable.")
     *         )
     *     )
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
     *     path="/matches/{id}/close",
     *     summary="Clôturer un match",
     *     description="Clôture un match et met à jour le classement du groupe. Seul l'organisateur peut clôturer un match. Tous les matchs de la semaine précédente doivent être clôturés. Si c'est la finale, la compétition passe en 'finished' et les awards sont attribués.",
     *     tags={"Matchs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du match",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match clôturé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Match clôturé avec succès."),
     *             @OA\Property(property="home_team", type="string", example="Les Lions 8"),
     *             @OA\Property(property="away_team", type="string", example="Les Lions 3"),
     *             @OA\Property(property="home_score", type="integer", example=2),
     *             @OA\Property(property="away_score", type="integer", example=1),
     *             @OA\Property(property="result", type="string", enum={"Victoire domicile","Victoire extérieur","Match nul"}, example="Victoire domicile")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Match déjà clôturé ou semaine précédente non complète",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tous les matchs de la semaine précédente doivent être clôturés.")
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
    public function closeMatch(string $id): JsonResponse
    {
        try {
            $result = $this->matchService->closeMatch($id);
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