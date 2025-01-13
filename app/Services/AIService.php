<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private $apiKey;
    private $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
    }

    public function anonymizeComment(string $comment): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'), // Requis par OpenRouter
                'X-Title' => 'Evaluation System', // Nom de votre application
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'mistralai/mistral-7b-instruct', // Modèle gratuit/peu coûteux
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un expert en reformulation de texte. Reformule le commentaire en gardant le même sens mais en supprimant tout élément qui pourrait identifier l\'auteur. Le ton doit rester professionnel et constructif.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $comment
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            Log::error('Erreur OpenRouter:', $response->json());
            return $comment;
        } catch (\Exception $e) {
            Log::error('Exception OpenRouter:', ['message' => $e->getMessage()]);
            return $comment;
        }
    }
}
