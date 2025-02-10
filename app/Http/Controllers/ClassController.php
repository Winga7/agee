<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'name' => 'required|string|unique:class_groups,name'
        ]);

        // Selon votre structure de base de données, adaptez cette partie
        DB::table('class_groups')->insert([
            'name' => $validated['name'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('message', 'Classe créée avec succès');
    }

    public function update(Request $request, $className)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:class_groups,name,' . $className . ',name'
        ]);

        DB::table('class_groups')
            ->where('name', $className)
            ->update([
                'name' => $validated['name'],
                'updated_at' => now()
            ]);

        return redirect()->back()->with('message', 'Classe mise à jour avec succès');
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
