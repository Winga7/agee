<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EvaluationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // On récupère toutes les évaluations avec le module associé
        // (et l’utilisateur si on a un user_id, à voir selon l’anonymisation)
        $evaluations = Evaluation::with('module')->get();

        return Inertia::render('Evaluations/Index', [
            'evaluations' => $evaluations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Liste des modules pour que l’utilisateur choisisse
        // ou bien on fait l’évaluation depuis la page du module
        $modules = Module::all();

        return Inertia::render('Evaluations/Create', [
            'modules' => $modules,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Selon l’anonymisation, on ne stocke pas user_id, ou on le crypte...
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'score'     => 'required|integer|between:1,5',
            'comment'   => 'nullable|string',
        ]);

        Evaluation::create([
            'module_id' => $request->module_id,
            'score'     => $request->score,
            'comment'   => $request->comment,
            // 'user_id' => Auth::id(), // si besoin de lier à l’utilisateur
        ]);

        return to_route('evaluations.index')
            ->with('success', 'Évaluation créée avec succès !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Evaluation $evaluation)
    {
        $evaluation->load('module');

        return Inertia::render('Evaluations/Show', [
            'evaluation' => $evaluation,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evaluation $evaluation)
    {
        // Récupérer la liste des modules si on veut changer le module lié
        $modules = Module::all();

        return Inertia::render('Evaluations/Edit', [
            'evaluation' => $evaluation,
            'modules'    => $modules,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Evaluation $evaluation)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'score'     => 'required|integer|between:1,5',
            'comment'   => 'nullable|string',
        ]);

        $evaluation->update([
            'module_id' => $request->module_id,
            'score'     => $request->score,
            'comment'   => $request->comment,
        ]);

        return to_route('evaluations.index')
            ->with('success', 'Évaluation mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();

        return to_route('evaluations.index')
            ->with('success', 'Évaluation supprimée avec succès !');
    }
}
