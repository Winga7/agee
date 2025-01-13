@component('mail::message')
# Invitation à évaluer votre cours

Vous êtes invité(e) à évaluer le cours : **{{ $moduleName }}**

Cette évaluation est totalement anonyme et nous permettra d'améliorer la qualité de nos formations.

@component('mail::button', ['url' => $evaluationUrl])
Évaluer le cours
@endcomponent

Ce lien expirera le {{ $expiresAt }}.

Merci,<br>
{{ config('app.name') }}
@endcomponent
