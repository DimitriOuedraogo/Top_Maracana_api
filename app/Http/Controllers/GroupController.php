<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Groupes", description="Gestion des groupes de la phase de poules")
 */
class GroupController extends Controller
{
    /**
     * @OA\Get(
     *     path="/groups",
     *     summary="Lister tous les groupes",
     *     tags={"Groupes"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste de tous les groupes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="groups", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Groupe A"),
     *                     @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="competition", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                         @OA\Property(property="status", type="string", enum={"registration_open","full","ongoing","finished"}, example="ongoing")
     *                     ),
     *                     @OA\Property(property="teams", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                             @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                             @OA\Property(property="logo", type="string", nullable=true, example=null),
     *                             @OA\Property(property="pivot", type="object",
     *                                 @OA\Property(property="played", type="integer", example=3),
     *                                 @OA\Property(property="win", type="integer", example=2),
     *                                 @OA\Property(property="draws", type="integer", example=1),
     *                                 @OA\Property(property="losses", type="integer", example=0),
     *                                 @OA\Property(property="goals_for", type="integer", example=5),
     *                                 @OA\Property(property="goals_against", type="integer", example=2),
     *                                 @OA\Property(property="goal_difference", type="integer", example=3),
     *                                 @OA\Property(property="points", type="integer", example=7)
     *                             ),
     *                             @OA\Property(property="players", type="array",
     *                                 @OA\Items(
     *                                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                                     @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                                     @OA\Property(property="birth_date", type="string", format="date", example="2000-01-01"),
     *                                     @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                                     @OA\Property(property="national_id_number", type="string", nullable=true, example=null),
     *                                     @OA\Property(property="national_id_photo", type="string", nullable=true, example=null)
     *                                 )
     *                             )
     *                         )
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
            $groups = Group::with(['competition', 'teams.players'])->get();
            return response()->json(['success' => true, 'groups' => $groups], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/groups/{id}",
     *     summary="Détail d'un groupe avec ses équipes et joueurs",
     *     tags={"Groupes"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du groupe",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail du groupe",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="group", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Groupe A"),
     *                 @OA\Property(property="competition_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="competition", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                     @OA\Property(property="status", type="string", example="ongoing")
     *                 ),
     *                 @OA\Property(property="teams", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null),
     *                         @OA\Property(property="pivot", type="object",
     *                             @OA\Property(property="played", type="integer", example=3),
     *                             @OA\Property(property="win", type="integer", example=2),
     *                             @OA\Property(property="draws", type="integer", example=1),
     *                             @OA\Property(property="losses", type="integer", example=0),
     *                             @OA\Property(property="goals_for", type="integer", example=5),
     *                             @OA\Property(property="goals_against", type="integer", example=2),
     *                             @OA\Property(property="goal_difference", type="integer", example=3),
     *                             @OA\Property(property="points", type="integer", example=7)
     *                         ),
     *                         @OA\Property(property="players", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                                 @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                                 @OA\Property(property="birth_date", type="string", format="date", example="2000-01-01"),
     *                                 @OA\Property(property="is_goalkeeper", type="boolean", example=false),
     *                                 @OA\Property(property="national_id_number", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="national_id_photo", type="string", nullable=true, example=null)
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Groupe introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Groupe introuvable.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $group = Group::with(['competition', 'teams.players'])->find($id);

            if (!$group) {
                throw new \Exception('Groupe introuvable.', 404);
            }

            return response()->json(['success' => true, 'group' => $group], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/groups/{id}/matches",
     *     summary="Matchs d'un groupe",
     *     tags={"Groupes"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID du groupe",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des matchs du groupe",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="group", type="string", example="Groupe A"),
     *             @OA\Property(property="matches", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="round_type", type="string", enum={"group","semi","final"}, example="group"),
     *                     @OA\Property(property="status", type="string", enum={"scheduled","played"}, example="scheduled"),
     *                     @OA\Property(property="week_number", type="integer", example=1),
     *                     @OA\Property(property="day_of_week", type="integer", example=6, description="0=Dimanche, 6=Samedi"),
     *                     @OA\Property(property="match_time", type="string", example="10:00:00"),
     *                     @OA\Property(property="group_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="home_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     ),
     *                     @OA\Property(property="away_team", type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                         @OA\Property(property="name", type="string", example="Les Lions 2"),
     *                         @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Groupe introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Groupe introuvable.")
     *         )
     *     )
     * )
     */
    public function matches(string $id): JsonResponse
    {
        try {
            $group = Group::with([
                'matches.homeTeam',
                'matches.awayTeam',
            ])->find($id);

            if (!$group) {
                throw new \Exception('Groupe introuvable.', 404);
            }

            return response()->json([
                'success' => true,
                'group'   => $group->name,
                'matches' => $group->matches,
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