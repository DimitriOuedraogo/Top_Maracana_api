<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\KnockoutController;
// ── Auth ──────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', action: [AuthController::class, 'me']);
    });
});

// ── Compétitions ──────────────────────────────────────────────

Route::middleware('auth:api')->group(function () {
    Route::get('/competitions/my', [CompetitionController::class, 'myCompetitions']);
    Route::post('/competitions', [CompetitionController::class, 'store']);
    Route::post('/competitions/{id}', [CompetitionController::class, 'update']);
    Route::get('/competitions/{id}/knockout', [CompetitionController::class, 'knockoutMatches']);
    Route::delete('/competitions/{id}', [CompetitionController::class, 'destroy']);
});

Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{id}', [CompetitionController::class, 'show']);
Route::get('/competitions/{id}/groups', [CompetitionController::class, 'groups']);
Route::get('/competitions/{id}/matches', [CompetitionController::class, 'matches']);
Route::get('/competitions/{id}/statistics', [CompetitionController::class, 'statistics']);


// ── Équipes ──────────────────────────────────────────────
Route::get('/teams', [TeamController::class, 'index']);
Route::get('/teams/{id}', [TeamController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::get('/teams/my', [TeamController::class, 'myTeams']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::post('/teams/{id}', [TeamController::class, 'update']);
    Route::delete('/teams/{id}', [TeamController::class, 'destroy']);
});

// ── Groupes ───────────────────────────────────────────────
Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/{id}', [GroupController::class, 'show']);
Route::get('/groups/{id}/matches', [GroupController::class, 'matches']);

// ── Matchs ────────────────────────────────────────────────
Route::get('/matches', [MatchController::class, 'index']);
Route::get('/matches/{id}', [MatchController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/matches/{id}/goals', [MatchController::class, 'addGoal']);
    Route::post('/matches/{id}/cards', [MatchController::class, 'addCard']);
    Route::post('/matches/{id}/close', [MatchController::class, 'closeMatch']);
    Route::post('/matches/{id}/penalties', [KnockoutController::class, 'addPenalties']);

});

Route::middleware('auth:api')->group(function () {
    // ── Knockout ──────────────────────────────────────────────
    Route::post('/competitions/{id}/generate-knockout', [KnockoutController::class, 'generateKnockout']);
    Route::post('/competitions/{id}/next-round', [KnockoutController::class, 'generateNextRound']);
});

Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'message' => 'App is alive!']);
});