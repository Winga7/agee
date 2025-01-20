<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\CourseEnrollment;

class ModuleController extends Controller
{
    public function index()
    {
        return Module::with('professor')->get();
    }

    public function getGroups(Module $module)
    {
        return CourseEnrollment::where('module_id', $module->id)
            ->select('class_group')
            ->distinct()
            ->pluck('class_group');
    }
}
