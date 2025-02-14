<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Models\Module;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StudentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      $students = Student::with(['courseEnrollments.module', 'class'])
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
        });

      Log::info('Chargement de la liste des étudiants', [
        'count' => $students->count()
      ]);

      return Inertia::render('Students/Index', [
        'students' => $students,
        'modules' => Module::all(),
        'classes' => ClassGroup::select('id', 'name')->get()
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement des étudiants', [
        'error' => $e->getMessage()
      ]);
      return redirect()->back()->with('error', 'Erreur lors du chargement des étudiants');
    }
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
    try {
      DB::beginTransaction();

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

      Log::info('Tentative de création d\'un étudiant', $validated);

      $student = Student::create($validated);

      DB::commit();

      Log::info('Étudiant créé avec succès', [
        'student_id' => $student->id,
        'name' => $student->first_name . ' ' . $student->last_name
      ]);

      return redirect()->back()->with('success', 'L\'étudiant a été ajouté avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la création de l\'étudiant', [
        'error' => $e->getMessage(),
        'data' => $request->all()
      ]);
      return redirect()->back()
        ->with('error', 'Erreur lors de la création de l\'étudiant')
        ->withInput();
    }
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
    try {
      DB::beginTransaction();

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

      Log::info('Tentative de mise à jour de l\'étudiant', [
        'student_id' => $student->id,
        'data' => $validated
      ]);

      $student->update($validated);

      DB::commit();

      Log::info('Étudiant mis à jour avec succès', [
        'student_id' => $student->id,
        'name' => $student->first_name . ' ' . $student->last_name
      ]);

      return redirect()->back()->with('success', 'Étudiant mis à jour avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la mise à jour de l\'étudiant', [
        'error' => $e->getMessage(),
        'student_id' => $student->id,
        'data' => $request->all()
      ]);
      return redirect()->back()
        ->with('error', 'Erreur lors de la mise à jour de l\'étudiant')
        ->withInput();
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Student $student)
  {
    try {
      DB::beginTransaction();

      Log::info('Tentative de suppression de l\'étudiant', [
        'student_id' => $student->id,
        'name' => $student->first_name . ' ' . $student->last_name
      ]);

      $student->delete();

      DB::commit();

      Log::info('Étudiant supprimé avec succès', [
        'student_id' => $student->id
      ]);

      return redirect()->back()->with('success', 'Étudiant supprimé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la suppression de l\'étudiant', [
        'error' => $e->getMessage(),
        'student_id' => $student->id
      ]);
      return redirect()->back()->with('error', 'Erreur lors de la suppression de l\'étudiant');
    }
  }

  public function enroll(Request $request)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'module_id' => 'required|exists:modules,id',
        'class_id' => 'required|exists:class_groups,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
      ]);

      Log::info('Tentative d\'inscription d\'un étudiant à un module', $validated);

      $enrollment = CourseEnrollment::create($validated);

      DB::commit();

      Log::info('Inscription créée avec succès', [
        'enrollment_id' => $enrollment->id,
        'student_id' => $enrollment->student_id,
        'module_id' => $enrollment->module_id
      ]);

      return back()->with('success', 'Inscription créée avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de l\'inscription', [
        'error' => $e->getMessage(),
        'data' => $request->all()
      ]);
      return back()
        ->with('error', 'Erreur lors de la création de l\'inscription')
        ->withInput();
    }
  }
}
