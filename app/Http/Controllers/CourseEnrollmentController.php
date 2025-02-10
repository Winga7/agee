<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseEnrollmentController extends Controller
{
  public function store(Request $request)
  {
    $validated = $request->validate([
      'student_id' => 'required|exists:students,id',
      'module_id' => 'required|exists:modules,id',
      'class_id' => 'required|exists:class_groups,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
    ]);

    // S'assurer que toutes les données validées sont utilisées dans la création
    CourseEnrollment::create([
      'student_id' => $validated['student_id'],
      'module_id' => $validated['module_id'],
      'class_id' => $validated['class_id'],
      'start_date' => date('Y-m-d', strtotime($validated['start_date'])),
      'end_date' => date('Y-m-d', strtotime($validated['end_date']))
    ]);

    return redirect()->back()->with('success', 'Inscription créée avec succès');
  }

  public function destroy(CourseEnrollment $enrollment)
  {
    $enrollment->delete();
    return redirect()->back()->with('success', 'Inscription supprimée avec succès');
  }
}
