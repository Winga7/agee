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
                ->where('is_used', false)
                ->select([
                    'module_id',
                    'class_id',
                    'created_at',
                    'is_used',
                    'expires_at',
                    'id'
                ])
                ->get()
        ]);
    }
    /**
     * Affiche le formulaire d'évaluation pour un token donné
     */
    public function createWithToken($token)
    {
        $evaluationToken = EvaluationToken::where('token', $token)->firstOrFail();

        if (!$evaluationToken->isValid()) {
            if ($evaluationToken->isExpired()) {
                return back()->with('error', 'Ce lien d\'évaluation a expiré.');
            }
            return back()->with('error', 'Ce lien d\'évaluation a déjà été utilisé.');
        }

        return Inertia::render('Evaluations/CreateWithToken', [
            'token' => $token,
            'module' => $evaluationToken->module,
            'expires_at' => $evaluationToken->expires_at
        ]);
    }

    /**
     * Enregistre une évaluation créée avec un token
     */
    public function storeWithToken(Request $request, $token)
    {
        $evaluationToken = EvaluationToken::where('token', $token)->firstOrFail();

        if (!$evaluationToken->isValid()) {
            if ($evaluationToken->isExpired()) {
                return back()->with('error', 'Ce lien d\'évaluation a expiré.');
            }
            return back()->with('error', 'Ce lien d\'évaluation a déjà été utilisé.');
        }

        $validated = $request->validate([
            'score' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Anonymisation du commentaire si présent
        if (!empty($validated['comment'])) {
            $anonymizedComment = $this->aiService->anonymizeComment($validated['comment']);
        }

        DB::beginTransaction();
        try {
            // Créer l'évaluation
            Evaluation::create([
                'module_id' => $evaluationToken->module_id,
                'score' => $validated['score'],
                'original_comment' => $validated['comment'] ?? null,
                'anonymized_comment' => $anonymizedComment ?? null,
                'is_anonymized' => !empty($anonymizedComment),
                'user_hash' => hash('sha256', $token . env('APP_KEY'))
            ]);

            // Marquer le token comme utilisé
            $evaluationToken->update(['is_used' => true]);

            DB::commit();
            return redirect()->route('evaluations.thank-you')
                ->with('success', 'Merci pour votre évaluation !');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement de votre évaluation.');
        }
    }

    public function generateTokensForGroup(Request $request)
    {
        try {
            $request->validate([
                'module_id' => 'required|exists:modules,id',
                'class_group' => 'required|string'
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
}
