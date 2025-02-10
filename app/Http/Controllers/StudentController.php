<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Models\Module;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return Inertia::render('Students/Index', [
      'students' => Student::with(['courseEnrollments.module', 'class'])
        ->get()
        ->map(function ($student) {
          return [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'school_email' => $student->school_email,
            'telephone' => $student->telephone,
            'birth_date' => $student->birth_date,
            'student_number' => $student->student_id,
            'class' => $student->class,
            'academic_year' => $student->academic_year,
            'status' => $student->status,
            'courseEnrollments' => $student->courseEnrollments
          ];
        }),
      'modules' => Module::all(),
      'classes' => ClassGroup::select('id', 'name')->get()
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|email|unique:students',
      'school_email' => 'nullable|email|unique:students',
      'telephone' => 'nullable|string',
      'birth_date' => 'required|date',
      'student_id' => 'required|string|unique:students',
      'class_id' => 'required|exists:class_groups,id',
      'academic_year' => 'required|string',
      'status' => 'required|in:active,inactive,graduated'
    ]);

    Student::create($validated);

    return redirect()->back()->with('success', 'L\'étudiant a été ajouté avec succès');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Student $student)
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:100',
      'last_name' => 'required|string|max:100',
      'email' => 'required|email|unique:students,email,' . $student->id,
      'school_email' => 'nullable|email|unique:students,school_email,' . $student->id,
      'telephone' => 'nullable|string',
      'birth_date' => 'required|date',
      'student_id' => 'required|string|unique:students,student_id,' . $student->id,
      'class_id' => 'required|exists:class_groups,id',
      'academic_year' => 'required|string',
      'status' => 'required|in:active,inactive,graduated'
    ]);

    $student->update($validated);
    return redirect()->back();
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Student $student)
  {
    $student->delete();
    return redirect()->back();
  }

  public function enroll(Request $request)
  {
    $validated = $request->validate([
      'student_id' => 'required|exists:students,id',
      'module_id' => 'required|exists:modules,id',
      'class_id' => 'required|exists:class_groups,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
    ]);

    CourseEnrollment::create($validated);
    return back()->with('success', 'Inscription créée avec succès');
  }
}
