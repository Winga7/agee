<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index()
    {
        return Inertia::render('Classes/Index', [
            'classes' => Classes::with(['students', 'modules'])->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:classes'
        ]);

        Classes::create($validated);
        return redirect()->back()->with('success', 'Classe créée avec succès');
    }

    public function update(Request $request, Classes $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:classes,name,' . $class->id
        ]);

        $class->update($validated);
        return redirect()->back()->with('success', 'Classe mise à jour avec succès');
    }

    public function destroy(Classes $class)
    {
        $class->delete();
        return redirect()->back()->with('success', 'Classe supprimée avec succès');
    }

    public function show(Classes $class)
    {
        return Inertia::render('Classes/Show', [
            'class' => $class->load(['students', 'modules']),
            'modules' => Module::all()
        ]);
    }
}
