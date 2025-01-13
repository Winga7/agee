<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Inertia\Inertia; // important pour retourner des vues Inertia
use Redirect;       // pour rediriger après les actions

class ProfessorController extends Controller
{
    // Liste des professeurs
    public function index()
    {
        $professors = Professor::all();
        return Inertia::render('Professors/Index', [
            'professors' => $professors
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
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'nullable|email',
            'department' => 'nullable|string|max:100',
        ]);

        Professor::create($request->all());
        return Redirect::route('professors.index');
    }

    // Afficher un professeur (optionnel, selon tes besoins)
    public function show(Professor $professor)
    {
        return Inertia::render('Professors/Show', [
            'professor' => $professor
        ]);
    }

    // Formulaire d’édition
    public function edit(Professor $professor)
    {
        return Inertia::render('Professors/Edit', [
            'professor' => $professor
        ]);
    }

    // Mettre à jour un professeur
    public function update(Request $request, Professor $professor)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'nullable|email',
            'department' => 'nullable|string|max:100',
        ]);

        $professor->update($request->all());
        return Redirect::route('professors.index');
    }

    // Supprimer un professeur
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return Redirect::route('professors.index');
    }
}