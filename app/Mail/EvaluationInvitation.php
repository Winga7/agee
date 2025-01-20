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
        return $this->markdown('emails.evaluation-invitation')
            ->with([
                'moduleName' => $this->token->module->title,
                'evaluationUrl' => route('evaluations.create-with-token', $this->token->token),
                'expiresAt' => $this->token->expires_at->format('d/m/Y H:i')
            ]);
    }
}
