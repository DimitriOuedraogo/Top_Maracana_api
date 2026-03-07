<?php

namespace App\Http\Controllers;

use App\Http\Requests\Competitions\StoreCompetitionRequest;
use App\Http\Requests\Competitions\UpdateCompetitionRequest;
use App\Services\CompetitionService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Compétitions", description="Gestion des compétitions")
 */
class CompetitionController extends Controller
{
    public function __construct(
        protected CompetitionService $competitionService
    ) {}

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
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                         @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                         @OA\Property(property="status", type="string", enum={"registration_open","full","ongoing","finished"}, example="registration_open"),
     *                         @OA\Property(property="start_date", type="string", format="date", example="2026-03-15"),
     *                         @OA\Property(property="end_date", type="string", format="date", example="2026-03-25"),
     *                         @OA\Property(property="max_teams", type="integer", example=8),
     *                         @OA\Property(property="players_per_team", type="integer", example=11),
     *                         @OA\Property(property="registration_fee", type="number", example=5000),
     *                         @OA\Property(property="prize_description", type="string", nullable=true, example="Trophée + 500 000 FCFA"),
     *                         @OA\Property(property="age_min", type="integer", nullable=true, example=18),
     *                         @OA\Property(property="age_max", type="integer", nullable=true, example=40),
     *                         @OA\Property(property="poster_image", type="string", nullable=true, example="competitions/poster.jpg"),
     *                         @OA\Property(property="winner_id", type="string", format="uuid", nullable=true, example=null),
     *                         @OA\Property(property="top_scorer_id", type="string", format="uuid", nullable=true, example=null),
     *                         @OA\Property(property="best_player_id", type="string", format="uuid", nullable=true, example=null),
     *                         @OA\Property(property="best_goalkeeper_id", type="string", format="uuid", nullable=true, example=null)
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
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                     @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                     @OA\Property(property="status", type="string", enum={"registration_open","full","ongoing","finished"}, example="ongoing"),
     *                     @OA\Property(property="start_date", type="string", format="date", example="2026-03-15"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2026-03-25"),
     *                     @OA\Property(property="max_teams", type="integer", example=8),
     *                     @OA\Property(property="players_per_team", type="integer", example=11),
     *                     @OA\Property(property="registration_fee", type="number", example=5000),
     *                     @OA\Property(property="prize_description", type="string", nullable=true, example=null),
     *                     @OA\Property(property="age_min", type="integer", nullable=true, example=null),
     *                     @OA\Property(property="age_max", type="integer", nullable=true, example=null),
     *                     @OA\Property(property="poster_image", type="string", nullable=true, example=null),
     *                     @OA\Property(property="winner_id", type="string", format="uuid", nullable=true, example=null),
     *                     @OA\Property(property="top_scorer_id", type="string", format="uuid", nullable=true, example=null),
     *                     @OA\Property(property="best_player_id", type="string", format="uuid", nullable=true, example=null),
     *                     @OA\Property(property="best_goalkeeper_id", type="string", format="uuid", nullable=true, example=null)
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
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détail de la compétition",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                 @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                 @OA\Property(property="status", type="string", enum={"registration_open","full","ongoing","finished"}, example="finished"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2026-03-15"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2026-03-25"),
     *                 @OA\Property(property="max_teams", type="integer", example=8),
     *                 @OA\Property(property="players_per_team", type="integer", example=11),
     *                 @OA\Property(property="registration_fee", type="number", example=5000),
     *                 @OA\Property(property="prize_description", type="string", nullable=true, example="Trophée + 500 000 FCFA"),
     *                 @OA\Property(property="age_min", type="integer", nullable=true, example=18),
     *                 @OA\Property(property="age_max", type="integer", nullable=true, example=40),
     *                 @OA\Property(property="poster_image", type="string", nullable=true, example="competitions/poster.jpg"),
     *                 @OA\Property(property="matches_per_day", type="integer", example=2),
     *                 @OA\Property(property="winner_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="top_scorer_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="best_player_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440002"),
     *                 @OA\Property(property="best_goalkeeper_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440003"),
     *                 @OA\Property(property="winner", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Les Lions 8")
     *                 ),
     *                 @OA\Property(property="top_scorer", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="full_name", type="string", example="Joueur 1"),
     *                     @OA\Property(property="team", type="object",
     *                         @OA\Property(property="name", type="string", example="Les Lions 8")
     *                     )
     *                 ),
     *                 @OA\Property(property="best_player", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                     @OA\Property(property="full_name", type="string", example="Joueur 2"),
     *                     @OA\Property(property="team", type="object",
     *                         @OA\Property(property="name", type="string", example="Les Lions 5")
     *                     )
     *                 ),
     *                 @OA\Property(property="best_goalkeeper", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                     @OA\Property(property="full_name", type="string", example="Gardien 1"),
     *                     @OA\Property(property="team", type="object",
     *                         @OA\Property(property="name", type="string", example="Les Lions 3")
     *                     )
     *                 )
     *             )
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
     *                 required={"name","location","start_date","end_date","max_teams","players_per_team","days","time_slots","matches_per_day"},
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                 @OA\Property(property="location", type="string", example="Ouagadougou"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2026-04-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2026-04-30"),
     *                 @OA\Property(property="max_teams", type="integer", example=8),
     *                 @OA\Property(property="players_per_team", type="integer", example=11),
     *                 @OA\Property(property="matches_per_day", type="integer", example=2),
     *                 @OA\Property(property="days", type="array", @OA\Items(type="integer"), example={6,0}, description="Jours de match : 0=Dimanche, 6=Samedi"),
     *                 @OA\Property(property="time_slots", type="array", @OA\Items(type="string"), example={"10:00","15:00"}, description="Créneaux horaires des matchs"),
     *                 @OA\Property(property="registration_fee", type="number", nullable=true, example=5000, description="Frais d'inscription. Par défaut : 0"),
     *                 @OA\Property(property="prize_description", type="string", nullable=true, example="Trophée + 500 000 FCFA"),
     *                 @OA\Property(property="age_min", type="integer", nullable=true, example=18),
     *                 @OA\Property(property="age_max", type="integer", nullable=true, example=40),
     *                 @OA\Property(property="poster_image", type="string", format="binary", nullable=true, description="Image d'affiche (jpeg, png, jpg) max 2Mo")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compétition créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana"),
     *                 @OA\Property(property="status", type="string", example="registration_open")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Le nom de la compétition est obligatoire.")
     *         )
     *     )
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
     *     summary="Modifier une compétition (multipart/form-data)",
     *     tags={"Compétitions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", nullable=true, example="Tournoi Maracana 2026"),
     *                 @OA\Property(property="location", type="string", nullable=true, example="Bobo-Dioulasso"),
     *                 @OA\Property(property="start_date", type="string", format="date", nullable=true, example="2026-04-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2026-04-30"),
     *                 @OA\Property(property="max_teams", type="integer", nullable=true, example=16),
     *                 @OA\Property(property="players_per_team", type="integer", nullable=true, example=11),
     *                 @OA\Property(property="registration_fee", type="number", nullable=true, example=5000),
     *                 @OA\Property(property="prize_description", type="string", nullable=true, example="Trophée + 1 000 000 FCFA"),
     *                 @OA\Property(property="age_min", type="integer", nullable=true, example=18),
     *                 @OA\Property(property="age_max", type="integer", nullable=true, example=40),
     *                 @OA\Property(property="poster_image", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compétition modifiée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Tournoi Maracana 2026")
     *             )
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
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas autorisé à modifier cette compétition.")
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
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compétition supprimée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compétition supprimée avec succès.")
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
     *             @OA\Property(property="message", type="string", example="Vous n'êtes pas autorisé à supprimer cette compétition.")
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
     *     path="/competitions/{id}/groups",
     *     summary="Groupes et équipes d'une compétition",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Groupes de la compétition avec équipes et joueurs",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="string", example="Tournoi Maracana"),
     *             @OA\Property(property="groups", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Groupe A"),
     *                     @OA\Property(property="teams", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
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
     *                                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
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
     *     ),
     *     @OA\Response(response=404, description="Compétition introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compétition introuvable.")
     *         )
     *     )
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
     *     path="/competitions/{id}/matches",
     *     summary="Programme des matchs d'une compétition groupés par semaine",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programme des matchs par semaine et par jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="string", example="Tournoi Maracana"),
     *             @OA\Property(property="programme", type="object",
     *                 @OA\Property(property="1", type="object",
     *                     @OA\Property(property="week", type="string", example="Semaine 1"),
     *                     @OA\Property(property="matches", type="object",
     *                         @OA\Property(property="6", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                                 @OA\Property(property="round_type", type="string", enum={"group","semi","final"}, example="group"),
     *                                 @OA\Property(property="status", type="string", enum={"scheduled","played"}, example="scheduled"),
     *                                 @OA\Property(property="week_number", type="integer", example=1),
     *                                 @OA\Property(property="day_of_week", type="integer", example=6, description="0=Dimanche, 6=Samedi"),
     *                                 @OA\Property(property="match_time", type="string", example="10:00:00"),
     *                                 @OA\Property(property="group_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                                 @OA\Property(property="home_team", type="object",
     *                                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
     *                                     @OA\Property(property="name", type="string", example="Les Lions 1"),
     *                                     @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                                 ),
     *                                 @OA\Property(property="away_team", type="object",
     *                                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440003"),
     *                                     @OA\Property(property="name", type="string", example="Les Lions 2"),
     *                                     @OA\Property(property="logo", type="string", nullable=true, example=null)
     *                                 ),
     *                                 @OA\Property(property="group", type="object", nullable=true,
     *                                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                                     @OA\Property(property="name", type="string", example="Groupe A")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
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

    /**
     * @OA\Get(
     *     path="/competitions/{id}/knockout",
     *     summary="Matchs éliminatoires d'une compétition groupés par tour",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Matchs éliminatoires groupés par tour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="string", example="Tournoi Maracana"),
     *             @OA\Property(property="knockout", type="object",
     *                 @OA\Property(property="semi", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="round_type", type="string", example="semi"),
     *                         @OA\Property(property="status", type="string", enum={"scheduled","played"}, example="played"),
     *                         @OA\Property(property="home_team", type="object",
     *                             @OA\Property(property="name", type="string", example="Les Lions 8")
     *                         ),
     *                         @OA\Property(property="away_team", type="object",
     *                             @OA\Property(property="name", type="string", example="Les Lions 3")
     *                         ),
     *                         @OA\Property(property="result", type="object", nullable=true,
     *                             @OA\Property(property="home_score", type="integer", example=2),
     *                             @OA\Property(property="away_score", type="integer", example=1),
     *                             @OA\Property(property="home_penalty_score", type="integer", nullable=true, example=null),
     *                             @OA\Property(property="away_penalty_score", type="integer", nullable=true, example=null)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="final", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="round_type", type="string", example="final"),
     *                         @OA\Property(property="status", type="string", example="played")
     *                     )
     *                 )
     *             )
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
    public function knockoutMatches(string $id): JsonResponse
    {
        try {
            $result = $this->competitionService->getKnockoutMatches($id);
            return response()->json(['success' => true, ...$result], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/competitions/{id}/statistics",
     *     summary="Statistiques d'une compétition",
     *     tags={"Compétitions"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="UUID de la compétition",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques de la compétition",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="competition", type="string", example="Tournoi Maracana"),
     *             @OA\Property(property="top_scorer", type="object", nullable=true,
     *                 @OA\Property(property="player", type="string", example="Joueur 1"),
     *                 @OA\Property(property="team", type="string", example="Les Lions 8"),
     *                 @OA\Property(property="goals", type="integer", example=5)
     *             ),
     *             @OA\Property(property="best_player", type="object", nullable=true,
     *                 @OA\Property(property="player", type="string", example="Joueur 2"),
     *                 @OA\Property(property="team", type="string", example="Les Lions 5"),
     *                 @OA\Property(property="goals", type="integer", example=3),
     *                 @OA\Property(property="yellow_cards", type="integer", example=0),
     *                 @OA\Property(property="red_cards", type="integer", example=0),
     *                 @OA\Property(property="score", type="integer", example=9)
     *             ),
     *             @OA\Property(property="best_goalkeeper", type="object", nullable=true,
     *                 @OA\Property(property="player", type="string", example="Gardien 1"),
     *                 @OA\Property(property="team", type="string", example="Les Lions 3"),
     *                 @OA\Property(property="goals_against", type="integer", example=1)
     *             )
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
    public function statistics(string $id): JsonResponse
    {
        try {
            $result = $this->competitionService->getStatistics($id);
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