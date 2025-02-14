<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Evaluation;
use App\Models\EvaluationToken;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
  /**
   * Affiche le tableau de bord avec les statistiques
   *
   * @param Request $request
   * @return \Inertia\Response
   */
  public function dashboard(Request $request)
  {
    try {
      // Calcul de l'année académique courante
      $currentYear = date('Y');
      // Si nous sommes entre janvier et août, l'année académique a commencé l'année précédente
      $currentAcademicYear = (date('n') >= 1 && date('n') <= 8) ? $currentYear - 1 : $currentYear;
      $years = range($currentAcademicYear - 2, $currentAcademicYear + 1);

      Log::info('Génération du tableau de bord', [
        'current_academic_year' => $currentAcademicYear,
        'year_range' => $years
      ]);

      // Récupération des statistiques filtrées
      $stats = $this->getFilteredStats($request);

      return Inertia::render('Dashboard', [
        'stats' => $stats,
        'modules' => Module::select('id', 'title')->get(),
        'years' => $years,
        'currentAcademicYear' => $currentAcademicYear
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur lors de la génération du tableau de bord', [
        'error' => $e->getMessage()
      ]);

      return Inertia::render('Dashboard', [
        'error' => 'Une erreur est survenue lors du chargement des données'
      ]);
    }
  }

  /**
   * Récupère les statistiques filtrées selon les paramètres
   *
   * @param Request $request
   * @return array
   */
  public function getFilteredStats(Request $request)
  {
    try {
      $year = $request->input('year');
      $moduleId = $request->input('module_id');

      Log::info('Récupération des statistiques filtrées', [
        'year' => $year,
        'module_id' => $moduleId
      ]);

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

      $stats = [
        'totalModules' => $moduleId ? 1 : Module::whereHas('evaluations')->count(),
        'totalEvaluations' => $query->count(),
        'completedEvaluations' => (clone $query)->where('status', 'completed')->count(),
        'participationRate' => $this->calculateParticipationRate($moduleId, $year)
      ];

      Log::info('Statistiques générées', $stats);

      return $stats;
    } catch (\Exception $e) {
      Log::error('Erreur lors du calcul des statistiques', [
        'error' => $e->getMessage(),
        'year' => $request->input('year'),
        'module_id' => $request->input('module_id')
      ]);

      return [
        'totalModules' => 0,
        'totalEvaluations' => 0,
        'completedEvaluations' => 0,
        'participationRate' => 0
      ];
    }
  }

  /**
   * Calcule le taux de participation aux évaluations
   *
   * @param int|null $moduleId
   * @param int|null $year
   * @return int
   */
  private function calculateParticipationRate($moduleId = null, $year = null)
  {
    try {
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
      $rate = round(($completed / $total) * 100);

      Log::info('Calcul du taux de participation', [
        'module_id' => $moduleId,
        'year' => $year,
        'total' => $total,
        'completed' => $completed,
        'rate' => $rate
      ]);

      return $rate;
    } catch (\Exception $e) {
      Log::error('Erreur lors du calcul du taux de participation', [
        'error' => $e->getMessage(),
        'module_id' => $moduleId,
        'year' => $year
      ]);

      return 0;
    }
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
