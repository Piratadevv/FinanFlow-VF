<?php

use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EscompteController;
use App\Http\Controllers\RefinancementController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\StateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Configuration
    Route::get('/configuration', [ConfigurationController::class, 'show']);
    Route::put('/configuration', [ConfigurationController::class, 'update']);
    Route::post('/configuration/reset', [ConfigurationController::class, 'reset']);
    Route::post('/configuration/validate-autorisation', [ConfigurationController::class, 'validateAutorisation']);
    Route::post('/configuration/calculate-impact', [ConfigurationController::class, 'calculateImpact']);

    // Escomptes — export must be defined BEFORE resource-style {id} route
    Route::get('/escomptes/export', [EscompteController::class, 'export']);
    Route::post('/escomptes/recalculate', [EscompteController::class, 'recalculate']);
    Route::get('/escomptes', [EscompteController::class, 'index']);
    Route::get('/escomptes/{id}', [EscompteController::class, 'show']);
    Route::post('/escomptes', [EscompteController::class, 'store']);
    Route::put('/escomptes/{id}', [EscompteController::class, 'update']);
    Route::delete('/escomptes/{id}', [EscompteController::class, 'destroy']);

    // Refinancements
    Route::get('/refinancements/export', [RefinancementController::class, 'export']);
    Route::get('/refinancements', [RefinancementController::class, 'index']);
    Route::get('/refinancements/{id}', [RefinancementController::class, 'show']);
    Route::post('/refinancements', [RefinancementController::class, 'store']);
    Route::put('/refinancements/{id}', [RefinancementController::class, 'update']);
    Route::delete('/refinancements/{id}', [RefinancementController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard/kpi', [DashboardController::class, 'kpi']);

    // Logs
    Route::get('/logs/stats', [LogController::class, 'stats']);
    Route::get('/logs', [LogController::class, 'index']);
    Route::post('/logs', [LogController::class, 'store']);
    Route::delete('/logs/{id}', [LogController::class, 'destroy']);
    Route::delete('/logs', [LogController::class, 'destroyAll']);

    // State
    Route::post('/save-state', [StateController::class, 'saveState']);
    Route::get('/current-state', [StateController::class, 'currentState']);
});
