<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Professor;
use App\Models\ClassGroup;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
  /**
   * Affiche la liste des modules
   *
   * @return \Inertia\Response
   */
  public function index()
  {
    try {
      Log::info('Chargement de la page des modules');

      return Inertia::render('Modules/Index', [
        'modules' => Module::with(['professor', 'classes'])->get(),
        'professors' => Professor::all(),
        'classGroups' => ClassGroup::all()
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement des modules', [
        'error' => $e->getMessage()
      ]);
      return redirect()->back()->with('error', 'Erreur lors du chargement des modules');
    }
  }

  /**
   * Affiche le formulaire de création d'un module
   *
   * @return \Inertia\Response
   */
  public function create()
  {
    try {
      Log::info('Accès au formulaire de création de module');

      return Inertia::render('Modules/Create', [
        'professors' => Professor::all(),
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement du formulaire de création', [
        'error' => $e->getMessage()
      ]);
      return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire');
    }
  }

  /**
   * Enregistre un nouveau module
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'code' => 'required|string|max:50|unique:modules,code',
        'class_ids' => 'required|array',
        'class_ids.*' => 'exists:class_groups,id',
        'professor_id' => 'required|exists:professors,id'
      ]);

      Log::info('Tentative de création d\'un module', $validated);

      $module = Module::create($validated);

      if ($request->has('class_ids')) {
        $module->classes()->attach($request->class_ids);
      }

      DB::commit();

      Log::info('Module créé avec succès', [
        'module_id' => $module->id,
        'title' => $module->title,
        'code' => $module->code
      ]);

      return redirect()->route('modules.index')->with('success', 'Module créé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la création du module:', [
        'error' => $e->getMessage(),
        'data' => $request->all()
      ]);
      return redirect()->back()
        ->with('error', 'Erreur lors de la création du module')
        ->withInput();
    }
  }

  /**
   * Affiche les détails d'un module
   *
   * @param Module $module
   * @return \Inertia\Response
   */
  public function show(Module $module)
  {
    try {
      Log::info('Affichage du module', ['module_id' => $module->id]);

      return Inertia::render('Modules/Show', [
        'module' => $module->load(['professor', 'classes', 'courseEnrollments.student'])
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors de l\'affichage du module', [
        'error' => $e->getMessage(),
        'module_id' => $module->id
      ]);
      return redirect()->back()->with('error', 'Erreur lors de l\'affichage du module');
    }
  }

  /**
   * Affiche le formulaire d'édition d'un module
   *
   * @param Module $module
   * @return \Inertia\Response
   */
  public function edit(Module $module)
  {
    try {
      Log::info('Accès au formulaire d\'édition', ['module_id' => $module->id]);

      return Inertia::render('Modules/Edit', [
        'module' => $module,
        'professors' => Professor::all(),
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement du formulaire d\'édition', [
        'error' => $e->getMessage(),
        'module_id' => $module->id
      ]);
      return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire');
    }
  }

  /**
   * Met à jour un module existant
   *
   * @param Request $request
   * @param Module $module
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Module $module)
  {
    try {
      $validated = $request->validate([
        'title' => 'required|string|max:150',
        'code' => 'nullable|string|max:50|unique:modules,code,' . $module->id,
        'description' => 'nullable|string',
        'professor_id' => 'nullable|exists:professors,id',
        'class_ids' => 'required|array|min:1',
        'class_ids.*' => 'required|exists:class_groups,id'
      ]);

      Log::info('Tentative de mise à jour du module', [
        'module_id' => $module->id,
        'data' => $validated
      ]);

      $module->update($request->except('class_ids'));
      $module->classes()->sync($request->class_ids);

      Log::info('Module mis à jour avec succès', ['module_id' => $module->id]);

      return redirect()->back()->with('success', 'Module mis à jour avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la mise à jour du module', [
        'error' => $e->getMessage(),
        'module_id' => $module->id,
        'data' => $request->all()
      ]);
      return redirect()->back()->with('error', 'Erreur lors de la mise à jour du module');
    }
  }

  /**
   * Supprime un module
   *
   * @param Module $module
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Module $module)
  {
    try {
      Log::info('Tentative de suppression du module', ['module_id' => $module->id]);

      $module->delete();

      Log::info('Module supprimé avec succès', ['module_id' => $module->id]);

      return redirect()->back()->with('success', 'Module supprimé avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la suppression du module', [
        'error' => $e->getMessage(),
        'module_id' => $module->id
      ]);
      return redirect()->back()->with('error', 'Erreur lors de la suppression du module');
    }
  }

  /**
   * Récupère les groupes et étudiants d'un module
   *
   * @param int $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function getGroups($id)
  {
    try {
      $module = Module::with(['classes.students', 'courseEnrollments.student'])
        ->findOrFail($id);

      $groups = $module->classes->pluck('name')->unique()->values();

      $students = [];
      foreach ($module->classes as $class) {
        $students[$class->name] = CourseEnrollment::where('module_id', $module->id)
          ->where('class_id', $class->id)
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

      Log::info('Groupes et étudiants récupérés avec succès', [
        'module_id' => $id,
        'groups_count' => count($groups),
        'students_count' => collect($students)->flatten(1)->count()
      ]);

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
