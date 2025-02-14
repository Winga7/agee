<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CourseEnrollmentController extends Controller
{
  /**
   * Crée une nouvelle inscription à un cours
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    // Validation des données entrantes
    $validated = $request->validate([
      'student_id' => 'required|exists:students,id',
      'module_id' => 'required|exists:modules,id',
      'class_id' => 'required|exists:class_groups,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
    ]);

    Log::info('Tentative de création d\'inscription', [
      'student_id' => $validated['student_id'],
      'module_id' => $validated['module_id'],
      'class_id' => $validated['class_id']
    ]);

    try {
      DB::beginTransaction();

      // Création de l'inscription avec formatage des dates
      $enrollment = CourseEnrollment::create([
        'student_id' => $validated['student_id'],
        'module_id' => $validated['module_id'],
        'class_id' => $validated['class_id'],
        'start_date' => date('Y-m-d', strtotime($validated['start_date'])),
        'end_date' => date('Y-m-d', strtotime($validated['end_date']))
      ]);

      DB::commit();

      Log::info('Inscription créée avec succès', [
        'enrollment_id' => $enrollment->id,
        'student_id' => $enrollment->student_id,
        'module_id' => $enrollment->module_id
      ]);

      return redirect()->back()->with('success', 'Inscription créée avec succès');
    } catch (\Exception $e) {
      DB::rollBack();

      Log::error('Erreur lors de la création de l\'inscription', [
        'error' => $e->getMessage(),
        'student_id' => $validated['student_id'],
        'module_id' => $validated['module_id']
      ]);

      return redirect()->back()->with('error', 'Erreur lors de la création de l\'inscription');
    }
  }

  /**
   * Met à jour une inscription existante
   *
   * @param Request $request
   * @param CourseEnrollment $enrollment
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, CourseEnrollment $enrollment)
  {
    try {
      DB::beginTransaction();

      // Validation des données entrantes
      $validated = $request->validate([
        'module_id' => 'required|exists:modules,id',
        'class_id' => 'required|exists:class_groups,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
      ]);

      Log::info('Tentative de mise à jour d\'inscription', [
        'enrollment_id' => $enrollment->id,
        'student_id' => $enrollment->student_id,
        'old_module_id' => $enrollment->module_id,
        'new_module_id' => $validated['module_id']
      ]);

      // Mise à jour de l'inscription avec formatage des dates
      $enrollment->update([
        'module_id' => $validated['module_id'],
        'class_id' => $validated['class_id'],
        'start_date' => date('Y-m-d', strtotime($validated['start_date'])),
        'end_date' => date('Y-m-d', strtotime($validated['end_date']))
      ]);

      DB::commit();

      Log::info('Inscription mise à jour avec succès', [
        'enrollment_id' => $enrollment->id,
        'student_id' => $enrollment->student_id,
        'module_id' => $enrollment->module_id
      ]);

      return redirect()->back()->with('success', 'Inscription mise à jour avec succès');
    } catch (\Exception $e) {
      DB::rollBack();

      Log::error('Erreur lors de la mise à jour de l\'inscription', [
        'error' => $e->getMessage(),
        'enrollment_id' => $enrollment->id,
        'student_id' => $enrollment->student_id
      ]);

      return redirect()->back()->with('error', 'Erreur lors de la mise à jour de l\'inscription');
    }
  }

  /**
   * Supprime une inscription existante
   *
   * @param CourseEnrollment $enrollment
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(CourseEnrollment $enrollment)
  {
    try {
      DB::beginTransaction();

      Log::info('Tentative de suppression d\'inscription', [
        'enrollment_id' => $enrollment->id,
        'student_id' => $enrollment->student_id,
        'module_id' => $enrollment->module_id
      ]);

      $enrollment->delete();

      DB::commit();

      Log::info('Inscription supprimée avec succès', [
        'enrollment_id' => $enrollment->id
      ]);

      return redirect()->back()->with('success', 'Inscription supprimée avec succès');
    } catch (\Exception $e) {
      DB::rollBack();

      Log::error('Erreur lors de la suppression de l\'inscription', [
        'error' => $e->getMessage(),
        'enrollment_id' => $enrollment->id
      ]);

      return redirect()->back()->with('error', 'Erreur lors de la suppression de l\'inscription');
    }
  }
}
