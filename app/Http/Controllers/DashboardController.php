<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Evaluation;
use App\Models\EvaluationToken;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $currentYear = date('Y');
        $currentAcademicYear = (date('n') >= 1 && date('n') <= 8) ? $currentYear - 1 : $currentYear;
        $years = range($currentAcademicYear - 2, $currentAcademicYear + 1);
        
        $stats = $this->getFilteredStats($request);
        
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'modules' => Module::select('id', 'title')->get(),
            'years' => $years,
            'currentAcademicYear' => $currentAcademicYear
        ]);
    }

    private function getFilteredStats(Request $request)
    {
        $year = $request->input('year');
        $moduleId = $request->input('module_id');

        // Base query pour les évaluations
        $query = Evaluation::query();

        // Filtre par année académique
        if ($year) {
            $startDate = "{$year}-09-01";
            $endDate = ($year + 1) . "-08-31";
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filtre par module
        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        // Calcul des modules actifs (ayant au moins une évaluation)
        $activeModules = Module::when($moduleId, function ($query) use ($moduleId) {
            return $query->where('id', $moduleId);
        })
        ->when($year, function ($query) use ($year, $startDate, $endDate) {
            return $query->whereHas('evaluations', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        })
        ->count();

        // Nombre total d'évaluations
        $totalEvaluations = (clone $query)->count();

        // Nombre d'évaluations complétées
        $completedEvaluations = (clone $query)->where('status', 'completed')->count();

        // Calcul du taux de participation
        $participationRate = $totalEvaluations > 0 
            ? round(($completedEvaluations / $totalEvaluations) * 100) 
            : 0;

        return [
            'totalModules' => $activeModules,
            'totalEvaluations' => $totalEvaluations,
            'completedEvaluations' => $completedEvaluations,
            'participationRate' => $participationRate
        ];
    }

    private function calculateParticipationRate($moduleId = null, $year = null)
    {
        $query = EvaluationToken::query()
            ->when($moduleId, function ($query) use ($moduleId) {
                return $query->where('module_id', $moduleId);
            })
            ->when($year, function ($query) use ($year) {
                $startDate = "{$year}-09-01";
                $endDate = ($year + 1) . "-08-31";
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            });

        $total = $query->count();
        if ($total === 0) return 0;
        
        $completed = $query->whereNotNull('used_at')->count();
        return round(($completed / $total) * 100);
    }

    private function getStats()
    {
        return [
            'totalModules' => Module::count(),
            'totalEvaluations' => Evaluation::count(),
            'completedEvaluations' => Evaluation::whereNotNull('created_at')->count(),
            'averageScore' => round(Evaluation::avg('score'), 2) ?? 0,
            'participationRate' => $this->calculateParticipationRate()
        ];
    }
}
