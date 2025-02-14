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
        // Si nous sommes entre janvier et août, l'année académique a commencé l'année précédente
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

    public function getFilteredStats(Request $request)
    {
        $year = $request->input('year');
        $moduleId = $request->input('module_id');

        $query = Evaluation::query();

        if ($year) {
            $startDate = "{$year}-09-01";
            $endDate = ($year + 1) . "-08-31";
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        return [
            'totalModules' => $moduleId ? 1 : Module::whereHas('evaluations')->count(),
            'totalEvaluations' => $query->count(),
            'completedEvaluations' => (clone $query)->where('status', 'completed')->count(),
            'participationRate' => $this->calculateParticipationRate($moduleId, $year)
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
            'participationRate' => $this->calculateParticipationRate()
        ];
    }
}
