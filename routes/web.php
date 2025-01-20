<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfessorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes protégées par authentification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Modules
    Route::resource('modules', ModuleController::class);

    // Professors
    Route::resource('professors', ProfessorController::class);

    // Routes pour le pédagogue uniquement
    Route::middleware(['role:pedagogue'])->group(function () {
        Route::get('/evaluations/manage', [EvaluationController::class, 'manage'])
            ->name('evaluations.manage');
        Route::post('/evaluations/generate-tokens', [EvaluationController::class, 'generateTokensForGroup'])
            ->name('evaluations.generate-tokens');
        Route::resource('evaluations', EvaluationController::class);
    });

    // Ajouter dans le groupe middleware auth
    Route::get('/api/modules/{module}/groups', [ModuleController::class, 'getGroups']);
});

// Routes publiques pour les évaluations par token
Route::get('/evaluate/{token}', [EvaluationController::class, 'createWithToken'])
    ->name('evaluations.create-with-token');
Route::post('/evaluate/{token}', [EvaluationController::class, 'store'])
    ->name('evaluations.store-with-token');
