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
        return Inertia::render('Evaluations/Index', [
            'evaluations' => Evaluation::with('module.professor')->get()
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
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'score' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Anonymisation du commentaire via notre service
        if (!empty($validated['comment'])) {
            $anonymizedComment = $this->aiService->anonymizeComment($validated['comment']);
        }

                Evaluation::create([
            'module_id' => $validated['module_id'],
            'score' => $validated['score'],
            'original_comment' => $validated['comment'] ?? null,
            'anonymized_comment' => $anonymizedComment ?? null,
            'is_anonymized' => !empty($anonymizedComment),
            'user_hash' => hash('sha256', auth()->id() . $validated['module_id'] . env('APP_KEY'))
        ]);

        return to_route('evaluations.index')
            ->with('success', 'Votre évaluation a été enregistrée avec succès.');
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

    public function generateTokensForGroup(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'class_group' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Récupérer les inscriptions uniques pour éviter les doublons
            $enrollments = CourseEnrollment::where('module_id', $request->module_id)
                ->where('class_group', $request->class_group)
                ->with('student')
                ->get()
                ->unique('student_id');

            Log::info('Enrollments trouvés:', [
                'count' => $enrollments->count(),
                'enrollments' => $enrollments->toArray()
            ]);

            if ($enrollments->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'Aucun étudiant trouvé pour ce module et cette classe.');
            }

            foreach ($enrollments as $enrollment) {
                // Invalider les anciens tokens
                EvaluationToken::where('student_email', $enrollment->student->email)
                    ->where('module_id', $request->module_id)
                    ->where('is_used', false)
                    ->update(['is_used' => true]);

                $token = EvaluationToken::create([
                    'token' => EvaluationToken::generateToken(),
                    'module_id' => $request->module_id,
                    'expires_at' => now()->addMinutes(10),
                    'student_email' => $enrollment->student->email,
                    'class_group' => $request->class_group
                ]);

                Mail::to($enrollment->student->email)->send(new EvaluationInvitation($token));
            }

            DB::commit();
            return back()->with('success', 'Les invitations ont été envoyées avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création des tokens:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function manage()
    {
        $tokens = EvaluationToken::with('module')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($token) {
                return $token->module_id . '-' .
                    $token->class_group . '-' .
                    $token->created_at->format('Y-m-d H:i');
            })
            ->map(function ($group) {
                $first = $group->first();
                $now = now();

                $statusCounts = $group->groupBy(function ($token) {
                    return $token->getStatus();
                });

                return [
                    'id' => $first->id,
                    'module' => $first->module,
                    'class_group' => $first->class_group,
                    'created_at' => $first->created_at,
                    'total_sent' => $group->count(),
                    'completed' => $statusCounts->get('completed', collect())->count(),
                    'expired' => $statusCounts->get('expired', collect())->count(),
                    'pending' => $statusCounts->get('pending', collect())->count()
                ];
            })
            ->values();

        return Inertia::render('Evaluations/Manage', [
            'modules' => Module::with('professor')->get(),
            'sentTokens' => $tokens
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
}
