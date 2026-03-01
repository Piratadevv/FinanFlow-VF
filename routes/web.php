<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EscompteController;
use App\Http\Controllers\RefinancementController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', [LoginController::class , 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class , 'login']);

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class , 'logout'])->name('logout');

    Route::get('/', fn() => redirect('/dashboard'));

    Route::get('/dashboard', [DashboardController::class , 'analytics'])->name('dashboard');

    Route::get('/escomptes', function () {
            return view('escomptes.index');
        }
        )->name('escomptes.index');

        Route::get('/refinancements', function () {
            return view('refinancements.index');
        }
        )->name('refinancements.index');

        Route::get('/logs', function () {
            return view('logs.index');
        }
        )->name('logs.index');

        // Settings page
        Route::get('/parametres', [ParametresController::class , 'index'])->name('parametres.index');

        // ── JSON API endpoints (same-origin, session-based auth) ──────────
    
        // Configuration
        Route::get('/api/configuration', [ConfigurationController::class , 'show']);
        Route::put('/api/configuration', [ConfigurationController::class , 'update']);
        Route::post('/api/configuration/reset', [ConfigurationController::class , 'reset']);
        Route::post('/api/configuration/validate-autorisation', [ConfigurationController::class , 'validateAutorisation']);
        Route::post('/api/configuration/calculate-impact', [ConfigurationController::class , 'calculateImpact']);

        // Escomptes — export must be defined BEFORE {id} route
        Route::get('/api/escomptes/export', [EscompteController::class , 'export']);
        Route::post('/api/escomptes/recalculate', [EscompteController::class , 'recalculate']);
        Route::get('/api/escomptes', [EscompteController::class , 'index']);
        Route::get('/api/escomptes/{id}', [EscompteController::class , 'show']);
        Route::post('/api/escomptes', [EscompteController::class , 'store']);
        Route::put('/api/escomptes/{id}', [EscompteController::class , 'update']);
        Route::delete('/api/escomptes/{id}', [EscompteController::class , 'destroy']);

        // Refinancements
        Route::get('/api/refinancements/export', [RefinancementController::class , 'export']);
        Route::get('/api/refinancements', [RefinancementController::class , 'index']);
        Route::get('/api/refinancements/{id}', [RefinancementController::class , 'show']);
        Route::post('/api/refinancements', [RefinancementController::class , 'store']);
        Route::put('/api/refinancements/{id}', [RefinancementController::class , 'update']);
        Route::delete('/api/refinancements/{id}', [RefinancementController::class , 'destroy']);

        // Dashboard
        Route::get('/api/dashboard/kpi', [DashboardController::class , 'kpi']);

        // Logs
        Route::get('/api/logs/export', [LogController::class , 'export']);
        Route::get('/api/logs/stats', [LogController::class , 'stats']);
        Route::get('/api/logs', [LogController::class , 'index']);
        Route::post('/api/logs', [LogController::class , 'store']);
        Route::delete('/api/logs/{id}', [LogController::class , 'destroy']);
        Route::delete('/api/logs', [LogController::class , 'destroyAll']);

        // Account
        Route::put('/api/account/profile', [AccountController::class , 'updateProfile']);
        Route::put('/api/account/password', [AccountController::class , 'updatePassword']);

        // Users
        Route::get('/api/users', [UserController::class , 'index']);
        Route::post('/api/users', [UserController::class , 'store']);
        Route::put('/api/users/{id}', [UserController::class , 'update']);
        Route::delete('/api/users/{id}', [UserController::class , 'destroy']);

        // State
        Route::post('/api/save-state', [StateController::class , 'saveState']);
        Route::get('/api/current-state', [StateController::class , 'currentState']);    });
