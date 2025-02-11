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
    public function index()
    {
        return Inertia::render('Forms/Index', [
            'forms' => Form::with(['sections.questions' => function ($query) {
                $query->orderBy('order');
            }])->get()
        ]);
    }

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
                // Créer la section sans dépendance pour l'instant
                $section = $form->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'],
                    'depends_on_question_id' => null,
                    'depends_on_answer' => null
                ]);

                // Créer les questions pour cette section
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

                    // Stocker la correspondance entre l'ID temporaire et l'ID réel
                    if (isset($questionData['id'])) {
                        $sectionsMap[$questionData['id']] = $question->id;
                    }
                }

                // Stocker les infos de dépendance pour mise à jour ultérieure
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
            return redirect()->back()->with('success', 'Formulaire créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du formulaire: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création du formulaire')
                ->withInput();
        }
    }

    public function update(Request $request, Form $form)
    {
        try {
            DB::beginTransaction();

            // 1. Mise à jour du formulaire principal
            $form->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            // 2. Supprimer toutes les sections existantes et leurs questions
            $form->sections()->delete();

            // 3. Créer les nouvelles sections sans dépendances
            $sectionsMap = [];
            foreach ($request->sections as $sectionData) {
                $section = $form->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'],
                    'depends_on_question_id' => null, // On le mettra à jour plus tard
                    'depends_on_answer' => null
                ]);

                // Créer les questions pour cette section
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

                    // Stocker la correspondance entre l'ID temporaire et l'ID réel
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

            // 4. Mettre à jour les dépendances après que toutes les questions soient créées
            foreach ($sectionsToUpdate ?? [] as $sectionInfo) {
                if (isset($sectionsMap[$sectionInfo['temp_question_id']])) {
                    $sectionInfo['section']->update([
                        'depends_on_question_id' => $sectionsMap[$sectionInfo['temp_question_id']],
                        'depends_on_answer' => $sectionInfo['answer']
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Formulaire mis à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du formulaire: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du formulaire')
                ->withInput();
        }
    }

    public function destroy(Form $form)
    {
        try {
            $form->sections()->delete();
            $form->delete();

            return redirect()->route('forms.index')
                ->with('success', 'Le formulaire a été supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('forms.index')
                ->with('error', 'Une erreur est survenue lors de la suppression du formulaire.');
        }
    }

    public function toggleActive(Form $form)
    {
        $form->update(['is_active' => !$form->is_active]);
        return back();
    }
}
