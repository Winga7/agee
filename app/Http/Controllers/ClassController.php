<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClassController extends Controller
{
  /**
   * Affiche la liste des classes avec leurs modules associés
   *
   * @return \Inertia\Response
   */
  public function index()
  {
    return Inertia::render('Classes/Index', [
      'classGroups' => ClassGroup::with('modules')->get(), // Chargement eager des relations
      'modules' => Module::with('classes')->get()
    ]);
  }

  /**
   * Crée une nouvelle classe
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|unique:class_groups,name'
    ]);

    try {
      DB::table('class_groups')->insert([
        'name' => $validated['name'],
        'created_at' => now(),
        'updated_at' => now()
      ]);

      Log::info('Nouvelle classe créée', [
        'name' => $validated['name']
      ]);

      return redirect()->back()->with('message', 'Classe créée avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la création de la classe', [
        'error' => $e->getMessage(),
        'name' => $validated['name']
      ]);

      return redirect()->back()->with('error', 'Erreur lors de la création de la classe');
    }
  }

  /**
   * Met à jour une classe existante
   *
   * @param Request $request
   * @param string $className
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, $className)
  {
    $validated = $request->validate([
      'name' => 'required|string|unique:class_groups,name,' . $className . ',name'
    ]);

    try {
      DB::table('class_groups')
        ->where('name', $className)
        ->update([
          'name' => $validated['name'],
          'updated_at' => now()
        ]);

      Log::info('Classe mise à jour', [
        'old_name' => $className,
        'new_name' => $validated['name']
      ]);

      return redirect()->back()->with('message', 'Classe mise à jour avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la mise à jour de la classe', [
        'error' => $e->getMessage(),
        'old_name' => $className,
        'new_name' => $validated['name']
      ]);

      return redirect()->back()->with('error', 'Erreur lors de la mise à jour de la classe');
    }
  }

  /**
   * Supprime une classe
   *
   * @param ClassGroup $class
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(ClassGroup $class)
  {
    try {
      $class->delete();

      Log::info('Classe supprimée', [
        'class_id' => $class->id,
        'name' => $class->name
      ]);

      return redirect()->back()->with('success', 'Classe supprimée avec succès');
    } catch (\Exception $e) {
      Log::error('Erreur lors de la suppression de la classe', [
        'error' => $e->getMessage(),
        'class_id' => $class->id,
        'name' => $class->name
      ]);

      return redirect()->back()->with('error', 'Erreur lors de la suppression de la classe');
    }
  }

  /**
   * Affiche les détails d'une classe spécifique
   *
   * @param ClassGroup $class
   * @return \Inertia\Response
   */
  public function show(ClassGroup $class)
  {
    return Inertia::render('Classes/Show', [
      'class' => $class->load(['students', 'modules']), // Chargement eager des relations
      'modules' => Module::all()
    ]);
  }
}
