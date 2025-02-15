<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationToken;
use App\Models\Form;
use App\Models\Module;
use App\Models\Student;
use App\Models\CourseEnrollment;
use App\Models\ClassGroup;
use App\Services\AIService;
use App\Mail\EvaluationInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluationController extends Controller
{
  private $aiService;

  /**
   * Constructeur avec injection du service AI
   *
   * @param AIService $aiService
   */
  public function __construct(AIService $aiService)
  {
    $this->aiService = $aiService;
  }

  /**
   * Enregistre une nouvelle évaluation
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'module_id' => 'required|exists:modules,id',
        'class_id' => 'required|exists:classes,id'
      ]);

      Log::info('Début de création d\'évaluation', $validated);

      $enrollments = CourseEnrollment::where('module_id', $validated['module_id'])
        ->where('class_id', $validated['class_id'])
        ->with('student')
        ->get();

      foreach ($enrollments as $enrollment) {
        // Invalider les anciens tokens
        EvaluationToken::where('student_email', $enrollment->student->email)
          ->where('module_id', $validated['module_id'])
          ->where('class_id', $validated['class_id'])
          ->where('is_used', false)
          ->update(['is_used' => true]);

        // Créer et envoyer un nouveau token
        $token = EvaluationToken::create([
          'token' => Str::random(32),
          'student_email' => $enrollment->student->email,
          'module_id' => $validated['module_id'],
          'class_id' => $validated['class_id'],
          'expires_at' => now()->addDays(7),
        ]);

        Mail::to($enrollment->student->email)
          ->send(new EvaluationInvitation($token));

        Log::info('Token créé et envoyé', [
          'token_id' => $token->id,
          'student_email' => $enrollment->student->email
        ]);
      }

      DB::commit();

      Log::info('Évaluation créée avec succès', [
        'enrollments_count' => $enrollments->count()
      ]);

      return redirect()->back()->with('success', 'Invitations envoyées avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur lors de la création de l\'évaluation', [
        'error' => $e->getMessage(),
        'request_data' => $request->all()
      ]);
      return redirect()->back()->with('error', 'Erreur lors de l\'envoi des invitations');
    }
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
      'forms' => Form::all()
    ]);
  }
  /**
   * Affiche le formulaire d'évaluation pour un token donné
   */
  public function createWithToken($token)
  {
    try {
      Log::info('Début createWithToken', [
        'token' => $token
      ]);

      $evaluationToken = EvaluationToken::where('token', $token)
        ->with(['module', 'form' => function ($query) {
          $query->withTrashed();
        }, 'form.sections.questions'])
        ->firstOrFail();

      Log::info('Token trouvé', [
        'token_data' => [
          'id' => $evaluationToken->id,
          'module_id' => $evaluationToken->module_id,
          'form_id' => $evaluationToken->form_id,
          'student_email' => $evaluationToken->student_email,
          'expires_at' => $evaluationToken->expires_at,
          'is_used' => $evaluationToken->is_used
        ]
      ]);

      if (!$evaluationToken->isValid()) {
        Log::warning('Token invalide', [
          'token' => $token,
          'is_used' => $evaluationToken->is_used,
          'expires_at' => $evaluationToken->expires_at
        ]);

        if ($evaluationToken->isExpired()) {
          Log::warning('Token expiré', [
            'token' => $token,
            'expires_at' => $evaluationToken->expires_at
          ]);
          return back()->with('error', 'Ce lien d\'évaluation a expiré.');
        }

        Log::warning('Token déjà utilisé', [
          'token' => $token,
          'used_at' => $evaluationToken->used_at
        ]);
        return back()->with('error', 'Ce lien d\'évaluation a déjà été utilisé.');
      }

      Log::info('Rendu de la page d\'évaluation', [
        'module_id' => $evaluationToken->module_id,
        'form_id' => $evaluationToken->form_id
      ]);

      return Inertia::render('Evaluations/CreateWithToken', [
        'token' => $token,
        'module' => $evaluationToken->module,
        'form' => $evaluationToken->form,
        'expires_at' => $evaluationToken->expires_at
      ]);
    } catch (\Exception $e) {
      Log::error('Erreur dans createWithToken', [
        'token' => $token,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return back()->with('error', 'Une erreur est survenue lors du chargement de l\'évaluation.');
    }
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
      ->with(['form' => function ($query) {
        $query->withTrashed();
      }, 'module'])
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
        // Déterminer l'email à utiliser
        $emailToUse = $enrollment->student->school_email ?? $enrollment->student->email;

        // Invalider les anciens tokens non utilisés
        EvaluationToken::where('student_email', $emailToUse)
          ->where('module_id', $module->id)
          ->where('class_id', $class->id)
          ->where('is_used', false)
          ->update(['is_used' => true]);

        // Créer un nouveau token
        $token = EvaluationToken::create([
          'token' => Str::random(64),
          'module_id' => $module->id,
          'student_email' => $emailToUse,
          'class_id' => $class->id,
          'form_id' => $request->form_id, // Ajoutez cette ligne
          'expires_at' => now()->addDays(7),
          'is_used' => false
        ]);

        Mail::to($emailToUse)
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

      $form = Form::withTrashed()
        ->with('sections.questions')
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
        ->map(function ($token) use ($questionMap) {
          $userHash = hash('sha256', $token->token . $token->student_email . env('APP_KEY'));
          $evaluation = Evaluation::where('user_hash', $userHash)
            ->where('module_id', $token->module_id)
            ->first();

          // Formater les réponses avec les questions
          $formattedAnswers = null;
          if ($evaluation && $evaluation->answers) {
            $formattedAnswers = [];
            foreach ($evaluation->answers as $questionId => $answer) {
              $formattedAnswers[$questionId] = [
                'question' => $questionMap[$questionId] ?? "Question $questionId",
                'answer' => $answer
              ];
            }
          }

          return [
            'student_email' => $token->student_email,
            'used_at' => $token->used_at,
            'isExpired' => $token->isExpired(),
            'answers' => $formattedAnswers
          ];
        });

      return Inertia::render('Evaluations/Responses', [
        'tokens' => $tokens,
        'module' => Module::findOrFail($moduleId),
        'classGroup' => ClassGroup::findOrFail($classId),
        'date' => $date,
        'questions' => $questionMap
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

      // Récupérer les informations du module et de la classe
      $module = Module::findOrFail($moduleId);
      $class = ClassGroup::findOrFail($classId);

      // Le titre de l'onglet sera basé sur le module uniquement
      $sheetTitle = $module->code ?? "Module_$moduleId";

      // Créer le répertoire s'il n'existe pas
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }

      // Charger le fichier existant ou créer un nouveau
      if (file_exists($filePath)) {
        $spreadsheet = IOFactory::load($filePath);
        // Supprimer la feuille par défaut si c'est un nouveau fichier
        if ($spreadsheet->getSheetCount() === 1 && $spreadsheet->getSheet(0)->getHighestRow() === 1) {
          $spreadsheet->removeSheetByIndex(0);
        }
      } else {
        $spreadsheet = new Spreadsheet();
        // Supprimer la feuille par défaut
        $spreadsheet->removeSheetByIndex(0);
      }

      // Chercher si l'onglet existe déjà
      $sheetCourante = null;
      foreach ($spreadsheet->getAllSheets() as $sheet) {
        if ($sheet->getTitle() === $sheetTitle) {
          $sheetCourante = $sheet;
          break;
        }
      }

      // Si l'onglet n'existe pas, le créer
      if ($sheetCourante === null) {
        $sheetCourante = new Worksheet($spreadsheet, $sheetTitle);
        $spreadsheet->addSheet($sheetCourante);

        // Ajouter les en-têtes pour le nouvel onglet
        $headers = ['Horodatage'];
        foreach ($questionMap as $questionId => $question) {
          $headers[] = $question;
        }
        $sheetCourante->fromArray([$headers], null, 'A1');
      }

      // Activer l'onglet courant
      $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($sheetCourante));

      // Déterminer la première ligne vide
      $lastRow = $sheetCourante->getHighestRow();

      // Ajouter les nouvelles réponses
      foreach ($tokens as $token) {
        if (empty($token['answers'])) continue;

        $rowData = [
          $token['used_at']
        ];

        foreach ($questionMap as $questionId => $question) {
          $answer = $token['answers'][$questionId] ?? '';
          $rowData[] = is_array($answer) ? implode(', ', $answer) : $answer;
        }

        $lastRow++;
        $sheetCourante->fromArray([$rowData], null, 'A' . $lastRow);
      }

      // Ajuster la largeur des colonnes
      foreach (range('A', $sheetCourante->getHighestColumn()) as $col) {
        $sheetCourante->getColumnDimension($col)->setAutoSize(true);
      }

      // Sauvegarder avec gestion des erreurs
      try {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);
      } catch (\Exception $e) {
        Log::error('Erreur sauvegarde Excel:', [
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString()
        ]);
        throw $e;
      } finally {
        // Nettoyer la mémoire
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
      }
    } catch (\Exception $e) {
      Log::error('Erreur export Excel:', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      throw $e;
    }
  }

  public function downloadExcel($moduleId, $classId, $date)
  {
    try {
      $searchDate = \Carbon\Carbon::parse($date)->format('Y-m-d');

      $firstToken = EvaluationToken::where('module_id', $moduleId)
        ->where('class_id', $classId)
        ->whereDate('created_at', $searchDate)
        ->first();

      if (!$firstToken) {
        return response()->json(['error' => 'Aucune donnée trouvée'], 404);
      }

      $form = Form::withTrashed()
        ->with('sections.questions')
        ->findOrFail($firstToken->form_id);

      // Créer le mapping des questions
      $questionMap = [];
      foreach ($form->sections as $section) {
        foreach ($section->questions as $question) {
          $questionMap[$question->id] = $question->question;
        }
      }

      // Récupérer les tokens avec leurs réponses
      $tokens = EvaluationToken::where('module_id', $moduleId)
        ->where('class_id', $classId)
        ->whereDate('created_at', $searchDate)
        ->get()
        ->map(function ($token) use ($questionMap) {
          $userHash = hash('sha256', $token->token . $token->student_email . env('APP_KEY'));
          $evaluation = Evaluation::where('user_hash', $userHash)
            ->where('module_id', $token->module_id)
            ->first();

          return [
            'used_at' => $token->used_at ? $token->used_at->format('Y-m-d H:i:s') : null,
            'answers' => $evaluation ? $evaluation->answers : null
          ];
        });

      // Générer le fichier Excel
      $this->exportToExcel($tokens, $moduleId, $classId, $date, $questionMap);

      // Télécharger le fichier
      $filePath = storage_path('app/public/exports/evaluations.xlsx');

      if (!file_exists($filePath)) {
        return response()->json(['error' => 'Erreur lors de la génération du fichier'], 404);
      }

      return response()->download($filePath);
    } catch (\Exception $e) {
      Log::error('Erreur lors du téléchargement:', ['error' => $e->getMessage()]);
      return response()->json(['error' => 'Erreur lors du téléchargement'], 500);
    }
  }
}
