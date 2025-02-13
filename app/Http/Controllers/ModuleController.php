<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Professor;
use App\Models\ClassGroup;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ModuleController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return Inertia::render('Modules/Index', [
      'modules' => Module::with(['professor', 'classes'])->get(),
      'professors' => Professor::all(),
      'classGroups' => ClassGroup::all()
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // On récupère la liste des professeurs pour peupler un select (optionnel)
    $professors = Professor::all();

    return Inertia::render('Modules/Create', [
      'professors' => $professors,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'required|string',
      'code' => 'required|string|max:50|unique:modules,code',
      'class_ids' => 'required|array',
      'class_ids.*' => 'exists:class_groups,id',
      'professor_id' => 'nullable|exists:professors,id'
    ]);

    try {
      // Créer le module avec toutes les données validées
      $module = Module::create([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'code' => $validated['code'],
        'professor_id' => $validated['professor_id'] // S'assurer que cette ligne est présente
      ]);

      // Attacher les classes au module
      $module->classes()->attach($validated['class_ids']);

      return redirect()->back()->with('success', 'Module créé avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la création du module:', ['error' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Erreur lors de la création du module');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Module $module)
  {
    return Inertia::render('Modules/Show', [
      'module' => $module->load(['professor', 'classes', 'courseEnrollments.student'])
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Module $module)
  {
    $professors = Professor::all();

    return Inertia::render('Modules/Edit', [
      'module'     => $module,
      'professors' => $professors,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Module $module)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:150',
      'code' => 'nullable|string|max:50|unique:modules,code,' . $module->id,
      'description' => 'nullable|string',
      'professor_id' => 'nullable|exists:professors,id',
      'class_ids' => 'required|array|min:1',
      'class_ids.*' => 'required|exists:class_groups,id'
    ]);

    $module->update($request->except('class_ids'));
    $module->classes()->sync($request->class_ids);

    return redirect()->back()->with('success', 'Module mis à jour avec succès');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Module $module)
  {
    $module->delete();
    return redirect()->back()->with('success', 'Module supprimé avec succès');
  }

  public function getGroups($id)
  {
    try {
      $module = Module::with(['classes.students', 'courseEnrollments.student'])->findOrFail($id);

      // Récupérer les classes avec leur nom
      $groups = $module->classes->pluck('name')->unique()->values();

      // Récupérer les étudiants par classe en utilisant la relation courseEnrollments
      $students = [];
      foreach ($module->classes as $class) {
        $students[$class->name] = CourseEnrollment::where('module_id', $module->id)
          ->where('class_id', $class->id)  // Changement de class_group_id à class_id pour correspondre à la structure
          ->with(['student', 'module'])
          ->get()
          ->map(function ($enrollment) {
            return [
              'student' => $enrollment->student,
              'enrollment' => [
                'id' => $enrollment->id,
                'start_date' => $enrollment->start_date,
                'end_date' => $enrollment->end_date
              ]
            ];
          });
      }

      return response()->json([
        'groups' => $groups,
        'students' => $students
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur dans getGroups:', [
        'error' => $e->getMessage(),
        'module_id' => $id,
        'trace' => $e->getTraceAsString()
      ]);
      return response()->json(['error' => 'Erreur lors de la récupération des groupes'], 500);
    }
  }
}
