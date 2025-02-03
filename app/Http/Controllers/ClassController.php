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
        $classes = Classes::with('modules')->get();
        $modules = Module::all();

        return Inertia::render('Classes/Index', [
            'classes' => $classes,
            'modules' => $modules
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:classes,name'
        ]);

        Classes::create([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'Classe créée avec succès');
    }

    public function update(Request $request, Classes $class)
    {
        $request->validate([
            'name' => 'required|string|unique:classes,name,' . $class->id
        ]);

        $class->update([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'Classe modifiée avec succès');
    }

    public function destroy(Classes $class)
    {
        // Vérifier s'il y a des inscriptions liées
        if ($class->courseEnrollments()->exists()) {
            return redirect()->back()->with('error', 'Impossible de supprimer cette classe car elle contient des étudiants inscrits');
        }

        $class->modules()->detach(); // Supprimer les relations avec les modules
        $class->delete();

        return redirect()->back()->with('success', 'Classe supprimée avec succès');
    }
}
