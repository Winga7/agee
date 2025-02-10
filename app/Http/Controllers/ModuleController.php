<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Professor;
use App\Models\Classes;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
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
      'classGroups' => Classes::all()
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
      'title' => 'required|string|max:150',
      'code' => 'nullable|string|max:50|unique:modules',
      'description' => 'nullable|string',
      'professor_id' => 'nullable|exists:professors,id',
      'class_ids' => 'required|array|min:1',
      'class_ids.*' => 'required|exists:classes,id'
    ]);

    $module = Module::create($request->except('class_ids'));
    $module->classes()->attach($request->class_ids);

    return redirect()->back()->with('success', 'Module créé avec succès');
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

  public function getGroups(Module $module)
  {
    // Récupérer tous les groupes distincts pour ce module
    $groups = CourseEnrollment::where('module_id', $module->id)
      ->select('class_group')
      ->distinct()
      ->get()
      ->pluck('class_group');

    // Récupérer tous les étudiants pour ce module, groupés par classe
    $students = CourseEnrollment::where('module_id', $module->id)
      ->with('student')
      ->get()
      ->unique('student_id') // Éviter les doublons d'étudiants
      ->groupBy('class_group');

    return response()->json([
      'groups' => $groups,
      'students' => $students
    ]);
  }
}
