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

// Evaluations
Route::resource('evaluations', EvaluationController::class);

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
