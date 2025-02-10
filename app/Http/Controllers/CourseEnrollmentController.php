<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CourseEnrollmentController extends Controller
{
  public function store(Request $request)
  {
    Log::info('Données reçues:', $request->all());

    $validated = $request->validate([
      'student_id' => 'required|exists:students,id',
      'module_id' => 'required|exists:modules,id',
      'class_id' => 'required|exists:class_groups,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
    ]);

    Log::info('Données validées:', $validated);

    try {
      $enrollment = CourseEnrollment::create([
        'student_id' => $validated['student_id'],
        'module_id' => $validated['module_id'],
        'class_id' => $validated['class_id'],
        'start_date' => date('Y-m-d', strtotime($validated['start_date'])),
        'end_date' => date('Y-m-d', strtotime($validated['end_date']))
      ]);

      Log::info('Inscription créée:', $enrollment->toArray());

      return redirect()->back()->with('success', 'Inscription créée avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la création:', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Erreur lors de la création de l\'inscription');
    }
  }

  public function destroy(CourseEnrollment $enrollment)
  {
    $enrollment->delete();
    return redirect()->back()->with('success', 'Inscription supprimée avec succès');
  }
}
