<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationToken;
use App\Services\AIService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\EvaluationInvitation;
use App\Models\CourseEnrollment;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Form;
use App\Models\ClassGroup;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EvaluationController extends Controller
{
  private $aiService;

  public function __construct(AIService $aiService)
  {
    $this->aiService = $aiService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $evaluations = Evaluation::with('module')->get();
    $modules = Module::all();

    return Inertia::render('Evaluations/Index', [
      'evaluations' => $evaluations,
      'modules' => $modules
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return Inertia::render('Evaluations/Create', [
      'modules' => \App\Models\Module::with('professor')->get()
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $request->validate([
      'module_id' => 'required|exists:modules,id',
      'class_id' => 'required|exists:classes,id'
    ]);

    try {
      DB::beginTransaction();

      $enrollments = CourseEnrollment::where('module_id', $request->module_id)
        ->where('class_id', $request->class_id)
        ->with('student')
        ->get();

      foreach ($enrollments as $enrollment) {
        // Invalider les anciens tokens
        EvaluationToken::where('student_email', $enrollment->student->email)
          ->where('module_id', $request->module_id)
          ->where('class_id', $request->class_id)
          ->where('is_used', false)
          ->update(['is_used' => true]);

        // Créer un nouveau token
        $token = Str::random(32);
        EvaluationToken::create([
          'token' => $token,
          'student_email' => $enrollment->student->email,
          'module_id' => $request->module_id,
          'class_id' => $request->class_id,
          'expires_at' => now()->addDays(7),
        ]);

        // TODO: Envoyer l'email avec le token
      }

      DB::commit();
      return back()->with('success', 'Invitations envoyées avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de l\'envoi des invitations:', ['error' => $e->getMessage()]);
      return back()->with('error', 'Une erreur est survenue lors de l\'envoi des invitations.');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Evaluation $evaluation)
  {
    // On ne montre que le commentaire anonymisé
    return Inertia::render('Evaluations/Show', [
      'evaluation' => $evaluation->load('module.professor')
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Evaluation $evaluation)
  {
    // Récupérer la liste des modules si on veut changer le module lié
    $modules = Module::all();

    return Inertia::render('Evaluations/Edit', [
      'evaluation' => $evaluation,
      'modules'    => $modules,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Evaluation $evaluation)
  {
    $request->validate([
      'module_id' => 'required|exists:modules,id',
      'score'     => 'required|integer|between:1,5',
      'comment'   => 'nullable|string',
    ]);

    $evaluation->update([
      'module_id' => $request->module_id,
      'score'     => $request->score,
      'comment'   => $request->comment,
      'status'    => 'completed'
    ]);

    return to_route('evaluations.index')
      ->with('success', 'Évaluation mise à jour avec succès !');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Evaluation $evaluation)
  {
    $evaluation->delete();

    return to_route('evaluations.index')
      ->with('success', 'Évaluation supprimée avec succès !');
  }

  public function manage()
  {
    return Inertia::render('Evaluations/Manage', [
      'modules' => Module::with('classes')->get(),
      'sentTokens' => EvaluationToken::with(['module', 'class'])
        ->select([
          'module_id',
          'class_id',
          'created_at',
          DB::raw('COUNT(*) as total_sent'),
          DB::raw('SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as completed'),
          DB::raw('SUM(CASE WHEN expires_at < NOW() THEN 1 ELSE 0 END) as is_expired')
        ])
        ->groupBy('module_id', 'class_id', 'created_at')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($token) {
          return [
            'module' => $token->module,
            'class' => $token->class,
            'module_id' => $token->module_id,
            'class_id' => $token->class_id,
            'created_at' => $token->created_at,
            'total_sent' => $token->total_sent,
            'completed' => (int)$token->completed,
            'is_expired' => (int)$token->is_expired
          ];
        }),
      'forms' => Form::where('is_active', true)->get()
    ]);
  }
  /**
   * Affiche le formulaire d'évaluation pour un token donné
   */
  public function createWithToken($token)
  {
    $evaluationToken = EvaluationToken::where('token', $token)
      ->with(['module', 'form.sections.questions'])
      ->firstOrFail();

    if (!$evaluationToken->isValid()) {
      if ($evaluationToken->isExpired()) {
        return back()->with('error', 'Ce lien d\'évaluation a expiré.');
      }
      return back()->with('error', 'Ce lien d\'évaluation a déjà été utilisé.');
    }

    return Inertia::render('Evaluations/CreateWithToken', [
      'token' => $token,
      'module' => $evaluationToken->module,
      'form' => $evaluationToken->form,
      'expires_at' => $evaluationToken->expires_at
    ]);
  }

  /**
   * Enregistre une évaluation créée avec un token
   */
  public function storeWithToken(Request $request, $token)
  {
    Log::info('Début storeWithToken', [
      'token' => $token,
      'request_data' => $request->all()
    ]);

    $evaluationToken = EvaluationToken::where('token', $token)
      ->with(['form', 'module'])
      ->firstOrFail();

    Log::info('Token trouvé', [
      'token_data' => $evaluationToken->toArray()
    ]);

    if (!$evaluationToken->isValid()) {
      Log::warning('Token invalide', [
        'is_used' => $evaluationToken->is_used,
        'expires_at' => $evaluationToken->expires_at
      ]);

      if ($evaluationToken->isExpired()) {
        return back()->with('error', 'Ce lien d\'évaluation a expiré.');
      }
      return back()->with('error', 'Ce lien d\'évaluation a déjà été utilisé.');
    }

    $validated = $request->validate([
      'answers' => 'required|array',
    ]);

    DB::beginTransaction();
    try {
      $evaluation = Evaluation::create([
        'module_id' => $evaluationToken->module_id,
        'form_id' => $evaluationToken->form_id,
        'answers' => $validated['answers'],
        'user_hash' => hash('sha256', $token . $evaluationToken->student_email . env('APP_KEY')),
        'status' => 'completed',
        'score' => 0
      ]);

      $evaluationToken->update([
        'is_used' => true,
        'used_at' => now()
      ]);

      DB::commit();

      Log::info('Évaluation créée avec succès', [
        'evaluation_id' => $evaluation->id
      ]);

      return Inertia::render('Evaluations/ThankYou');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de l\'enregistrement:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement.');
    }
  }

  public function generateTokensForGroup(Request $request)
  {
    try {
      $request->validate([
        'module_id' => 'required|exists:modules,id',
        'class_group' => 'required|string',
        'form_id' => 'required|exists:forms,id' // Ajoutez cette validation
      ]);

      DB::beginTransaction();

      $module = Module::findOrFail($request->module_id);
      $classGroup = $request->class_group;

      // Récupérer l'ID de la classe à partir du nom
      $class = DB::table('class_groups')->where('name', $classGroup)->first();

      if (!$class) {
        throw new \Exception('Classe non trouvée');
      }

      // Modifier cette partie pour utiliser class_id
      $enrollments = CourseEnrollment::where('module_id', $module->id)
        ->where('class_id', $class->id)
        ->with('student')
        ->get();

      $tokensGenerated = 0;
      $totalStudents = $enrollments->count();

      foreach ($enrollments as $enrollment) {
        // Invalider les anciens tokens non utilisés
        EvaluationToken::where('student_email', $enrollment->student->email)
          ->where('module_id', $module->id)
          ->where('class_id', $class->id)
          ->where('is_used', false)
          ->update(['is_used' => true]);

        // Créer un nouveau token
        $token = EvaluationToken::create([
          'token' => Str::random(64),
          'module_id' => $module->id,
          'student_email' => $enrollment->student->email,
          'class_id' => $class->id,
          'form_id' => $request->form_id, // Ajoutez cette ligne
          'expires_at' => now()->addDays(7),
          'is_used' => false
        ]);

        Mail::to($enrollment->student->email)
          ->send(new EvaluationInvitation($token));
        $tokensGenerated++;
      }

      DB::commit();
      return redirect()->back()->with('success', "Invitations envoyées avec succès ($tokensGenerated/$totalStudents)");
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la génération des tokens:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'envoi des invitations.');
    }
  }

  public function showResponses($moduleId, $classId, $date)
  {
    try {
      $searchDate = \Carbon\Carbon::parse($date)->format('Y-m-d');

      // Récupérer d'abord un token pour avoir accès au form_id
      $firstToken = EvaluationToken::where('module_id', $moduleId)
        ->where('class_id', $classId)
        ->whereDate('created_at', $searchDate)
        ->first();

      // Récupérer le formulaire avec ses questions
      $form = Form::with('sections.questions')
        ->findOrFail($firstToken->form_id);

      // Créer un mapping des IDs de questions vers leurs énoncés
      $questionMap = [];
      foreach ($form->sections as $section) {
        foreach ($section->questions as $question) {
          $questionMap[$question->id] = $question->question;
        }
      }

      $tokens = EvaluationToken::where('module_id', $moduleId)
        ->where('class_id', $classId)
        ->whereDate('created_at', $searchDate)
        ->get()
        ->map(function ($token) {
          $userHash = hash('sha256', $token->token . $token->student_email . env('APP_KEY'));
          $evaluation = Evaluation::where('user_hash', $userHash)
            ->where('module_id', $token->module_id)
            ->first();

          return [
            'student_email' => $token->student_email,
            'used_at' => $token->used_at,
            'isExpired' => $token->isExpired(),
            'answers' => $evaluation ? $evaluation->answers : null
          ];
        });

      // Passer le mapping des questions à la fonction d'export
      $this->exportToExcel($tokens, $moduleId, $classId, $date, $questionMap);

      return Inertia::render('Evaluations/Responses', [
        'tokens' => $tokens,
        'module' => Module::findOrFail($moduleId),
        'classGroup' => ClassGroup::findOrFail($classId),
        'date' => $date
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur:', ['message' => $e->getMessage()]);
      return redirect()->back()->with('error', 'Une erreur est survenue.');
    }
  }

  private function exportToExcel($tokens, $moduleId, $classId, $date, $questionMap)
  {
    try {
      $fileName = 'evaluations.xlsx';
      $directory = storage_path('app/public/exports');
      $filePath = $directory . '/' . $fileName;

      // Récupérer les informations du module et de la classe pour le titre de l'onglet
      $module = Module::findOrFail($moduleId);
      $class = ClassGroup::findOrFail($classId);

      // Le titre de l'onglet sera basé sur le module et la classe
      $sheetTitle = sprintf(
        '%s_%s',
        $module->code ?? $moduleId,
        $class->name ?? $classId
      );

      // Créer le répertoire s'il n'existe pas
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }

      // Charger le fichier existant ou en créer un nouveau
      if (file_exists($filePath)) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

        // Chercher si l'onglet pour ce module existe déjà
        $sheet = null;
        foreach ($spreadsheet->getAllSheets() as $worksheet) {
          if ($worksheet->getTitle() === $sheetTitle) {
            $sheet = $worksheet;
            break;
          }
        }

        // Si l'onglet n'existe pas, le créer
        if (!$sheet) {
          $spreadsheet->createSheet();
          $sheet = $spreadsheet->setActiveSheetIndex($spreadsheet->getSheetCount() - 1);
          $sheet->setTitle($sheetTitle);

          // Écrire les en-têtes pour le nouvel onglet
          $headers = ['Horodatage'];
          foreach ($questionMap as $questionId => $question) {
            $headers[] = $question;
          }
          $sheet->fromArray([$headers], null, 'A1');
        }
      } else {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        // Écrire les en-têtes pour le nouveau fichier
        $headers = ['Horodatage'];
        foreach ($questionMap as $questionId => $question) {
          $headers[] = $question;
        }
        $sheet->fromArray([$headers], null, 'A1');
      }

      // Trouver la dernière ligne utilisée dans l'onglet
      $lastRow = $sheet->getHighestRow();

      // Ajouter les nouvelles réponses à la suite
      foreach ($tokens as $token) {
        if (empty($token['answers'])) continue;

        $rowData = [
          $token['used_at']
        ];

        // Ajouter les réponses dans l'ordre des questions du mapping
        foreach ($questionMap as $questionId => $question) {
          $answer = $token['answers'][$questionId] ?? '';
          $rowData[] = is_array($answer) ? implode(', ', $answer) : $answer;
        }

        $lastRow++;
        $sheet->fromArray([$rowData], null, 'A' . $lastRow);
      }

      // Ajuster la largeur des colonnes
      foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      $writer = new Xlsx($spreadsheet);
      $writer->save($filePath);

      // Nettoyer la mémoire
      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
    } catch (\Exception $e) {
      Log::error('Erreur export Excel:', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
    }
  }
}
