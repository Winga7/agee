<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSection;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
  /**
   * Affiche la liste des formulaires
   *
   * @return \Inertia\Response
   */
  public function index()
  {
    try {
      $forms = Form::with(['sections.questions' => function ($query) {
        $query->orderBy('order');
      }])
        ->where('is_active', true)
        ->get();

      Log::info('Chargement de la liste des formulaires', [
        'forms_count' => $forms->count()
      ]);

      return Inertia::render('Forms/Index', [
        'forms' => $forms
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement des formulaires', [
        'error' => $e->getMessage()
      ]);
      return redirect()->back()->with('error', 'Erreur lors du chargement des formulaires');
    }
  }

  /**
   * Enregistre un nouveau formulaire
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    try {
      DB::beginTransaction();

      // 1. Créer le formulaire
      $form = Form::create([
        'title' => $request->title,
        'description' => $request->description,
        'is_active' => true
      ]);

      // 2. Créer les sections et questions sans dépendances
      $sectionsMap = [];
      foreach ($request->sections as $sectionData) {
        $section = $form->sections()->create([
          'title' => $sectionData['title'],
          'description' => $sectionData['description'] ?? null,
          'order' => $sectionData['order'],
          'depends_on_question_id' => null,
          'depends_on_answer' => null
        ]);

        foreach ($sectionData['questions'] as $questionData) {
          $question = $section->questions()->create([
            'form_id' => $form->id,
            'question' => $questionData['question'],
            'type' => $questionData['type'],
            'options' => $questionData['options'] ?? [],
            'order' => $questionData['order'],
            'is_required' => $questionData['is_required'],
            'controls_visibility' => $questionData['controls_visibility']
          ]);

          if (isset($questionData['id'])) {
            $sectionsMap[$questionData['id']] = $question->id;
          }
        }

        if (!empty($sectionData['depends_on_question_id'])) {
          $sectionsToUpdate[] = [
            'section' => $section,
            'temp_question_id' => $sectionData['depends_on_question_id'],
            'answer' => $sectionData['depends_on_answer']
          ];
        }
      }

      // 3. Mettre à jour les dépendances des sections
      foreach ($sectionsToUpdate ?? [] as $sectionInfo) {
        if (isset($sectionsMap[$sectionInfo['temp_question_id']])) {
          $sectionInfo['section']->update([
            'depends_on_question_id' => $sectionsMap[$sectionInfo['temp_question_id']],
            'depends_on_answer' => $sectionInfo['answer']
          ]);
        }
      }

      DB::commit();
      Log::info('Formulaire créé avec succès', ['form_id' => $form->id]);
      return redirect()->back()->with('success', 'Formulaire créé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la création du formulaire', [
        'error' => $e->getMessage()
      ]);
      return redirect()->back()
        ->with('error', 'Une erreur est survenue lors de la création du formulaire')
        ->withInput();
    }
  }

  /**
   * Met à jour un formulaire existant
   *
   * @param Request $request
   * @param Form $form
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Form $form)
  {
    try {
      DB::beginTransaction();

      $form->update([
        'title' => $request->title,
        'description' => $request->description,
      ]);

      // Supprimer toutes les sections existantes et leurs questions
      $form->sections()->delete();

      // Recréer les sections et questions
      $sectionsMap = [];
      foreach ($request->sections as $sectionData) {
        $section = $form->sections()->create([
          'title' => $sectionData['title'],
          'description' => $sectionData['description'] ?? null,
          'order' => $sectionData['order'],
          'depends_on_question_id' => null,
          'depends_on_answer' => null
        ]);

        foreach ($sectionData['questions'] as $questionData) {
          $question = $section->questions()->create([
            'form_id' => $form->id,
            'question' => $questionData['question'],
            'type' => $questionData['type'],
            'options' => $questionData['options'] ?? [],
            'order' => $questionData['order'],
            'is_required' => $questionData['is_required'],
            'controls_visibility' => $questionData['controls_visibility']
          ]);

          if (isset($questionData['id'])) {
            $sectionsMap[$questionData['id']] = $question->id;
          }
        }

        if (!empty($sectionData['depends_on_question_id'])) {
          $sectionsToUpdate[] = [
            'section' => $section,
            'temp_question_id' => $sectionData['depends_on_question_id'],
            'answer' => $sectionData['depends_on_answer']
          ];
        }
      }

      // Mettre à jour les dépendances
      foreach ($sectionsToUpdate ?? [] as $sectionInfo) {
        if (isset($sectionsMap[$sectionInfo['temp_question_id']])) {
          $sectionInfo['section']->update([
            'depends_on_question_id' => $sectionsMap[$sectionInfo['temp_question_id']],
            'depends_on_answer' => $sectionInfo['answer']
          ]);
        }
      }

      DB::commit();
      Log::info('Formulaire mis à jour avec succès', ['form_id' => $form->id]);
      return redirect()->back()->with('success', 'Formulaire mis à jour avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la mise à jour du formulaire', [
        'error' => $e->getMessage(),
        'form_id' => $form->id
      ]);
      return redirect()->back()
        ->with('error', 'Une erreur est survenue lors de la mise à jour du formulaire')
        ->withInput();
    }
  }

  /**
   * Supprime un formulaire
   *
   * @param Form $form
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Form $form)
  {
    try {
      // Désactiver le formulaire
      $form->update(['is_active' => false]);
      // Utiliser delete() pour déclencher le soft delete
      $form->delete();

      Log::info('Formulaire archivé avec succès', ['form_id' => $form->id]);
      return redirect()->route('forms.index')
        ->with('success', 'Le formulaire a été archivé avec succès.');
    } catch (\Exception $e) {
      Log::error('Erreur lors de l\'archivage du formulaire', [
        'error' => $e->getMessage(),
        'form_id' => $form->id
      ]);
      return back()->with('error', 'Une erreur est survenue lors de l\'archivage du formulaire.');
    }
  }

  /**
   * Active/désactive un formulaire
   *
   * @param Form $form
   * @return \Illuminate\Http\RedirectResponse
   */
  public function toggleActive(Form $form)
  {
    try {
      $form->update(['is_active' => !$form->is_active]);
      Log::info('Statut du formulaire modifié', [
        'form_id' => $form->id,
        'is_active' => $form->is_active
      ]);
      return back();
    } catch (\Exception $e) {
      Log::error('Erreur lors du changement de statut du formulaire', [
        'error' => $e->getMessage(),
        'form_id' => $form->id
      ]);
      return back()->with('error', 'Erreur lors du changement de statut');
    }
  }
}
