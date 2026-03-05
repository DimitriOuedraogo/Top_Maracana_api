<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/groups",
     *     summary="Lister tous les groupes",
     *     tags={"Groupes"},
     *     @OA\Response(response=200, description="Liste des groupes")
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
     *     path="/api/groups/{id}",
     *     summary="Détail d'un groupe",
     *     tags={"Groupes"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Détail du groupe"),
     *     @OA\Response(response=404, description="Groupe introuvable")
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
     *     path="/api/groups/{id}/matches",
     *     summary="Matchs d'un groupe",
     *     tags={"Groupes"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Matchs du groupe"),
     *     @OA\Response(response=404, description="Groupe introuvable")
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