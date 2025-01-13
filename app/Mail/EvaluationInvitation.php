<?php

namespace App\Mail;

use App\Models\EvaluationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaluationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected EvaluationToken $token
    ) {}

    public function build()
    {
        $evaluationUrl = route('evaluations.create-with-token', $this->token->token);

        return $this->markdown('emails.evaluation-invitation')
            ->subject('Invitation à évaluer votre cours')
            ->with([
                'moduleName' => $this->token->module->title,
                'evaluationUrl' => $evaluationUrl,
                'expiresAt' => $this->token->expires_at->format('d/m/Y')
            ]);
    }
}
