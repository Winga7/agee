<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProfessorController extends Controller
{
  /**
   * Affiche la liste des professeurs
   *
   * @return \Inertia\Response
   */
  public function index()
  {
    try {
      $professors = Professor::with('modules')->get();

      Log::info('Chargement de la liste des professeurs', [
        'count' => $professors->count()
      ]);

      return Inertia::render('Professors/Index', [
        'professors' => $professors
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement des professeurs', [
        'error' => $e->getMessage()
      ]);
      return redirect()->back()->with('error', 'Erreur lors du chargement des professeurs');
    }
  }

  // Formulaire de création
  public function create()
  {
    return Inertia::render('Professors/Create');
  }

  /**
   * Enregistre un nouveau professeur
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|unique:professors',
        'school_email' => 'nullable|email|unique:professors',
        'telephone' => 'nullable|string',
        'adress' => 'nullable|string|max:255',
        'birth_date' => 'nullable|date',
      ]);

      Log::info('Tentative de création d\'un professeur', $validated);

      $professor = Professor::create($validated);

      DB::commit();

      Log::info('Professeur créé avec succès', [
        'professor_id' => $professor->id,
        'name' => $professor->first_name . ' ' . $professor->last_name,
        'adress' => $professor->adress
      ]);

      return redirect()->back()->with('success', 'Professeur créé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la création du professeur', [
        'error' => $e->getMessage(),
        'data' => $request->all()
      ]);
      return redirect()->back()
        ->with('error', 'Erreur lors de la création du professeur')
        ->withInput();
    }
  }

  // Afficher un professeur (optionnel, selon tes besoins)
  public function show(Professor $professor)
  {
    return Inertia::render('Professors/Show', [
      'professor' => $professor->load('modules')
    ]);
  }

  // Formulaire d'édition
  public function edit(Professor $professor)
  {
    return Inertia::render('Professors/Edit', [
      'professor' => $professor
    ]);
  }

  /**
   * Met à jour un professeur existant
   *
   * @param Request $request
   * @param Professor $professor
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Professor $professor)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|unique:professors,email,' . $professor->id,
        'school_email' => 'nullable|email|unique:professors,school_email,' . $professor->id,
        'telephone' => 'nullable|string',
        'adress' => 'nullable|string|max:255',
        'birth_date' => 'nullable|date',
      ]);

      Log::info('Tentative de mise à jour du professeur', [
        'professor_id' => $professor->id,
        'data' => $validated
      ]);

      $professor->update($validated);

      DB::commit();

      Log::info('Professeur mis à jour avec succès', [
        'professor_id' => $professor->id,
        'name' => $professor->first_name . ' ' . $professor->last_name,
        'adress' => $professor->adress
      ]);

      return redirect()->back()->with('success', 'Professeur mis à jour avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la mise à jour du professeur', [
        'error' => $e->getMessage(),
        'professor_id' => $professor->id,
        'data' => $request->all()
      ]);
      return redirect()->back()
        ->with('error', 'Erreur lors de la mise à jour du professeur')
        ->withInput();
    }
  }

  /**
   * Supprime un professeur
   *
   * @param Professor $professor
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Professor $professor)
  {
    try {
      DB::beginTransaction();

      Log::info('Tentative de suppression du professeur', [
        'professor_id' => $professor->id,
        'name' => $professor->first_name . ' ' . $professor->last_name
      ]);

      $professor->delete();

      DB::commit();

      Log::info('Professeur supprimé avec succès', [
        'professor_id' => $professor->id
      ]);

      return redirect()->back()->with('success', 'Professeur supprimé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la suppression du professeur', [
        'error' => $e->getMessage(),
        'professor_id' => $professor->id
      ]);
      return redirect()->back()->with('error', 'Erreur lors de la suppression du professeur');
    }
  }
}
