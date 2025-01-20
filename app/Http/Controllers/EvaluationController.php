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

        // Récupérer tous les élèves inscrits à ce module dans ce groupe
        $enrollments = CourseEnrollment::where('module_id', $request->module_id)
            ->where('class_group', $request->class_group)
            ->where('end_date', '<=', now()) // Le cours doit être terminé
            ->with('student')
            ->get();

        foreach ($enrollments as $enrollment) {
            // Créer un token unique pour chaque élève
            $token = EvaluationToken::create([
                'token' => EvaluationToken::generateToken(),
                'module_id' => $request->module_id,
                'expires_at' => now()->addMinutes(10),
                'student_email' => $enrollment->student->email
            ]);

            // Envoyer l'email avec le lien d'évaluation
            Mail::to($enrollment->student->email)->send(new EvaluationInvitation($token));
        }

        return back()->with('success', 'Les invitations ont été envoyées avec succès.');
    }

    public function manage()
    {
        return Inertia::render('Evaluations/Manage', [
            'modules' => Module::with('professor')->get(),
            'sentTokens' => EvaluationToken::with('module')
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }
}
