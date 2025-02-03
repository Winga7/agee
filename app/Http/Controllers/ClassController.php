<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\ModuleClass;

class ClassController extends Controller
{
    public function index()
    {
        $classGroups = ModuleClass::select('class_group')->distinct()->pluck('class_group');
        $modules = Module::with('classes')->get();

        return Inertia::render('Classes/Index', [
            'classGroups' => $classGroups,
            'modules' => $modules
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:module_classes,class_group'
        ]);

        // La classe sera réellement créée lorsqu'elle sera associée à un module
        return back()->with('success', 'Classe créée avec succès');
    }

    public function update(Request $request, $oldName)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:module_classes,class_group'
        ]);

        ModuleClass::where('class_group', $oldName)
            ->update(['class_group' => $request->name]);

        return back()->with('success', 'Classe mise à jour avec succès');
    }

    public function destroy($name)
    {
        ModuleClass::where('class_group', $name)->delete();
        return back()->with('success', 'Classe supprimée avec succès');
    }
}
