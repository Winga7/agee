<?php

use App\Http\Controllers\{
  ClassController,
  CourseEnrollmentController,
  DashboardController,
  EvaluationController,
  FormController,
  ModuleController,
  ProfessorController,
  StudentController
};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

// Désactiver l'enregistrement
Fortify::registerView(null);

// Page d'accueil
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
  // Dashboard et API
  Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
  Route::get('/api/dashboard/stats', [DashboardController::class, 'getFilteredStats']);
  Route::get('/api/modules/{module}/groups', [ModuleController::class, 'getGroups']);

  // Ressources de base
  Route::resource('modules', ModuleController::class);
  Route::resource('professors', ProfessorController::class);
  Route::resource('students', StudentController::class);
  Route::resource('classes', ClassController::class);

  // Gestion des formulaires
  Route::prefix('forms')->group(function () {
    Route::get('/', [FormController::class, 'index'])->name('forms.index');
    Route::post('/', [FormController::class, 'store'])->name('forms.store');
    Route::put('/{form}', [FormController::class, 'update'])->name('forms.update');
    Route::delete('/{form}', [FormController::class, 'destroy'])->name('forms.destroy');
    Route::put('/{form}/toggle-active', [FormController::class, 'toggleActive'])->name('forms.toggle-active');
  });

  // Routes du pédagogue
  Route::middleware(['role:pedagogue'])->group(function () {
    Route::prefix('evaluations')->group(function () {
      Route::get('/manage', [EvaluationController::class, 'manage'])->name('evaluations.manage');
      Route::post('/generate-tokens', [EvaluationController::class, 'generateTokensForGroup'])->name('evaluations.generate-tokens');
      Route::get('/download/{module}/{class}/{date}', [EvaluationController::class, 'downloadExcel'])->name('evaluations.download');
      Route::resource('evaluations', EvaluationController::class);
    });
  });

  // Inscriptions aux cours
  Route::post('/students/enroll', [StudentController::class, 'enroll'])->name('students.enroll');
  Route::post('/enrollments', [CourseEnrollmentController::class, 'store'])->name('enrollments.store');
  Route::delete('/course-enrollments/{enrollment}', [CourseEnrollmentController::class, 'destroy'])->name('course-enrollments.destroy');
});

// Routes publiques des évaluations
Route::prefix('evaluate')->group(function () {
  Route::get('/{token}', [EvaluationController::class, 'createWithToken'])->name('evaluations.create-with-token');
  Route::post('/{token}', [EvaluationController::class, 'storeWithToken'])->name('evaluations.store-with-token');
});

// Routes des réponses aux évaluations
Route::prefix('evaluations')->group(function () {
  Route::get('/thank-you', function () {
    return Inertia::render('Evaluations/ThankYou');
  })->name('evaluations.thank-you');

  Route::get('/responses/{module}/{class}/{date}', [EvaluationController::class, 'showResponses'])
    ->name('evaluations.responses');
});
