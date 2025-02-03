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
            'class_id' => 'required|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        CourseEnrollment::create($validated);
        return redirect()->back()->with('success', 'Inscription créée avec succès');
    }

    public function destroy(CourseEnrollment $enrollment)
    {
        $enrollment->delete();
        return redirect()->back()->with('success', 'Inscription supprimée avec succès');
    }
}
