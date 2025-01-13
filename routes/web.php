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
// Modules
Route::resource('modules', ModuleController::class);

// Evaluations - Protégées par authentification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('evaluations', EvaluationController::class);

    // Route optionnelle pour vérifier si un utilisateur a déjà évalué un module
    Route::get('/evaluations/check/{module}', [EvaluationController::class, 'checkUserEvaluation'])
        ->name('evaluations.check');
});

// Routes publiques pour les évaluations par token
Route::get('/evaluate/{token}', [EvaluationController::class, 'createWithToken'])
    ->name('evaluations.create-with-token');
Route::post('/evaluate/{token}', [EvaluationController::class, 'store'])
    ->name('evaluations.store-with-token');

// Routes protégées pour l'administration des évaluations
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/evaluations/manage', [EvaluationController::class, 'manage'])
        ->name('evaluations.manage');
    Route::post('/evaluations/generate-tokens/{module}', [EvaluationController::class, 'generateTokens'])
        ->name('evaluations.generate-tokens');
});

// Professors
Route::resource('professors', ProfessorController::class);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
