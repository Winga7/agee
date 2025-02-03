<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Professor;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Modules/Index', [
            'modules' => Module::with(['professor', 'classes'])->get(),
            'professors' => Professor::all(),
            'classGroups' => ['Web Dev 1ère année', 'Web Dev 2e année', 'Web Dev 3e année']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // On récupère la liste des professeurs pour peupler un select (optionnel)
        $professors = Professor::all();

        return Inertia::render('Modules/Create', [
            'professors' => $professors,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'professor_id' => 'nullable|exists:professors,id',
            'class_groups' => 'required|array|min:1',
            'class_groups.*' => 'required|string'
        ]);

        $module = Module::create($request->except('class_groups'));

        foreach ($request->class_groups as $group) {
            $module->classes()->create(['class_group' => $group]);
        }

        return back()->with('success', 'Module créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        // On charge éventuellement les infos du professeur
        $module->load('professor');

        return Inertia::render('Modules/Show', [
            'module' => $module
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Module $module)
    {
        $professors = Professor::all();

        return Inertia::render('Modules/Edit', [
            'module'     => $module,
            'professors' => $professors,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'professor_id' => 'nullable|exists:professors,id',
            'class_groups' => 'required|array|min:1',
            'class_groups.*' => 'required|string'
        ]);

        $module->update($request->except('class_groups'));

        // Mettre à jour les classes
        $module->classes()->delete();
        foreach ($request->class_groups as $group) {
            $module->classes()->create(['class_group' => $group]);
        }

        return back()->with('success', 'Module mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $module->delete();

        return to_route('modules.index')
            ->with('success', 'Module supprimé avec succès !');
    }

    public function getGroups(Module $module)
    {
        // Récupérer tous les groupes distincts pour ce module
        $groups = CourseEnrollment::where('module_id', $module->id)
            ->select('class_group')
            ->distinct()
            ->get()
            ->pluck('class_group');

        // Récupérer tous les étudiants pour ce module, groupés par classe
        $students = CourseEnrollment::where('module_id', $module->id)
            ->with('student')
            ->get()
            ->unique('student_id') // Éviter les doublons d'étudiants
            ->groupBy('class_group');

        return response()->json([
            'groups' => $groups,
            'students' => $students
        ]);
    }
}
