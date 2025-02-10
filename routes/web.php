<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
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
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

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

    Route::get('/api/dashboard/stats', [DashboardController::class, 'getFilteredStats']);
    // Ajouter dans le groupe middleware auth
    Route::get('/api/modules/{module}/groups', [ModuleController::class, 'getGroups']);

    Route::middleware(['auth'])->group(function () {
        Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
        Route::post('/forms', [FormController::class, 'store'])->name('forms.store');
        Route::put('/forms/{form}', [FormController::class, 'update'])->name('forms.update');
        Route::delete('/forms/{form}', [FormController::class, 'destroy'])->name('forms.destroy');
        Route::put('/forms/{form}/toggle-active', [FormController::class, 'toggleActive'])->name('forms.toggle-active');
    });

    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
        Route::resource('students', StudentController::class);
        Route::post('/students/enroll', [StudentController::class, 'enroll'])->name('students.enroll');
        Route::resource('classes', ClassController::class);
    });

    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
        ->group(function () {
            Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
            Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
            Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
        });
});

// Routes publiques pour les évaluations par token
Route::get('/evaluate/{token}', [EvaluationController::class, 'createWithToken'])
    ->name('evaluations.create-with-token');
Route::post('/evaluate/{token}', [EvaluationController::class, 'store'])
    ->name('evaluations.store-with-token');
