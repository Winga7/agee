<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfessorController extends Controller
{
    // Liste des professeurs
    public function index()
    {
        return Inertia::render('Professors/Index', [
            'professors' => Professor::with('modules')->get()
        ]);
    }

    // Formulaire de création
    public function create()
    {
        return Inertia::render('Professors/Create');
    }

    // Stocker le nouveau professeur en BDD
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:professors',
            'school_email' => 'nullable|email|unique:professors',
            'telephone' => 'nullable|string',
            'adress' => 'nullable|string',
            'birth_date' => 'nullable|date',
        ]);

        Professor::create($validated);
        return redirect()->back()->with('success', 'Professeur créé avec succès');
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

    // Mettre à jour un professeur
    public function update(Request $request, Professor $professor)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:professors,email,' . $professor->id,
            'school_email' => 'nullable|email|unique:professors,school_email,' . $professor->id,
            'telephone' => 'nullable|string',
            'adress' => 'nullable|string',
            'birth_date' => 'nullable|date',
        ]);

        $professor->update($validated);
        return redirect()->back()->with('success', 'Professeur mis à jour avec succès');
    }

    // Supprimer un professeur
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->back()->with('success', 'Professeur supprimé avec succès');
    }
}
