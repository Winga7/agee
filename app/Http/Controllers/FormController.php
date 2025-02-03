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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'required|integer',
            'sections.*.depends_on_question_id' => 'nullable|integer',
            'sections.*.depends_on_answer' => 'nullable|string',
            'sections.*.questions' => 'required|array|min:1',
            'sections.*.questions.*.question' => 'required|string',
            'sections.*.questions.*.type' => 'required|in:text,textarea,radio,checkbox,select,rating',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.order' => 'required|integer',
            'sections.*.questions.*.is_required' => 'boolean',
            'sections.*.questions.*.controls_visibility' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Créer le formulaire
            $form = Form::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'is_active' => true
            ]);

            // Gérer l'upload de l'image bannière
            if ($request->hasFile('banner_image')) {
                $path = $request->file('banner_image')->store('form-banners', 'public');
                $form->update(['banner_image' => $path]);
            }

            // Créer les sections et leurs questions
            foreach ($validated['sections'] as $sectionData) {
                $section = $form->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'],
                    'depends_on_question_id' => $sectionData['depends_on_question_id'] ?? null,
                    'depends_on_answer' => $sectionData['depends_on_answer'] ?? null
                ]);

                // Créer les questions pour cette section
                foreach ($sectionData['questions'] as $questionData) {
                    Question::create([
                        'form_id' => $form->id,
                        'form_section_id' => $section->id,
                        'question' => $questionData['question'],
                        'type' => $questionData['type'],
                        'options' => $questionData['options'] ?? null,
                        'order' => $questionData['order'],
                        'is_required' => $questionData['is_required'] ?? true,
                        'controls_visibility' => $questionData['controls_visibility'] ?? false
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'required|integer',
            'sections.*.depends_on_question_id' => 'nullable|integer',
            'sections.*.depends_on_answer' => 'nullable|string',
            'sections.*.questions' => 'required|array|min:1',
            'sections.*.questions.*.question' => 'required|string',
            'sections.*.questions.*.type' => 'required|in:text,textarea,radio,checkbox,select,rating',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.order' => 'required|integer',
            'sections.*.questions.*.is_required' => 'boolean',
            'sections.*.questions.*.controls_visibility' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Gérer l'image bannière
            if ($request->hasFile('banner_image')) {
                if ($form->banner_image) {
                    Storage::disk('public')->delete($form->banner_image);
                }
                $bannerPath = $request->file('banner_image')->store('form-banners', 'public');
                $form->banner_image = $bannerPath;
                $form->save();
            }

            $form->update([
                'title' => $validated['title'],
                'description' => $validated['description']
            ]);

            // Supprimer les anciennes sections et questions
            $form->sections()->delete();

            // Recréer les sections et questions
            foreach ($validated['sections'] as $sectionData) {
                $section = $form->sections()->create([
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'],
                    'depends_on_question_id' => $sectionData['depends_on_question_id'] ?? null,
                    'depends_on_answer' => $sectionData['depends_on_answer'] ?? null
                ]);

                foreach ($sectionData['questions'] as $questionData) {
                    Question::create([
                        'form_id' => $form->id,
                        'form_section_id' => $section->id,
                        'question' => $questionData['question'],
                        'type' => $questionData['type'],
                        'options' => $questionData['options'] ?? null,
                        'order' => $questionData['order'],
                        'is_required' => $questionData['is_required'] ?? true,
                        'controls_visibility' => $questionData['controls_visibility'] ?? false
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Formulaire mis à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour du formulaire');
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
