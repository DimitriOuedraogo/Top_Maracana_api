<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MatchController;

// ── Auth ──────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {

    Route::post('/register',            [AuthController::class, 'register']); 
    Route::post('/login',               [AuthController::class, 'login']);
    Route::post('/verify-email',        [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/forgot-password',     [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-code',   [AuthController::class, 'verifyResetCode']);
    Route::post('/reset-password',      [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout',  [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me',       [AuthController::class, 'me']);
    });
});

// ── Compétitions ──────────────────────────────────────────────

Route::middleware('auth:api')->group(function () {
    Route::get('/competitions/my',      [CompetitionController::class, 'myCompetitions']);
    Route::post('/competitions',        [CompetitionController::class, 'store']);
    Route::post('/competitions/{id}',   [CompetitionController::class, 'update']);
    Route::delete('/competitions/{id}', [CompetitionController::class, 'destroy']);
});

Route::get('/competitions',        [CompetitionController::class, 'index']);
Route::get('/competitions/{id}',   [CompetitionController::class, 'show']);
Route::get('/competitions/{id}/groups',   [CompetitionController::class, 'groups']);
Route::get('/competitions/{id}/matches',  [CompetitionController::class, 'matches']);


// ── Équipes ──────────────────────────────────────────────
Route::get('/teams',        [TeamController::class, 'index']);
Route::get('/teams/{id}',   [TeamController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::get('/teams/my',       [TeamController::class, 'myTeams']);// Route non testée
    Route::post('/teams',         [TeamController::class, 'store']); // Route non testée
    Route::post('/teams/{id}',    [TeamController::class, 'update']); // Route non testée
    Route::delete('/teams/{id}',  [TeamController::class, 'destroy']); // Route non testée
});

// ── Groupes ───────────────────────────────────────────────
Route::get('/groups',           [GroupController::class, 'index']);
Route::get('/groups/{id}',      [GroupController::class, 'show']);
Route::get('/groups/{id}/matches', [GroupController::class, 'matches']);

// ── Matchs ────────────────────────────────────────────────
Route::get('/matches',      [MatchController::class, 'index']);
Route::get('/matches/{id}', [MatchController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/matches/{id}/goals', [MatchController::class, 'addGoal']);
    Route::post('/matches/{id}/cards', [MatchController::class, 'addCard']);
    Route::post('/matches/{id}/close', [MatchController::class, 'closeMatch']);
});