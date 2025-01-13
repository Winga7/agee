<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">
            Détails de l'Évaluation #{{ evaluation.id }}
        </h1>

        <p>
            <strong>Module :</strong>
            {{ evaluation.module ? evaluation.module.title : "N/A" }}
        </p>
        <p><strong>Score :</strong> {{ evaluation.score }}</p>
        <p><strong>Commentaire :</strong> {{ evaluation.comment }}</p>

        <Link
            href="/evaluations"
            class="underline text-blue-600 mt-4 inline-block"
        >
            Retour à la liste
        </Link>
    </div>
</template>

<script setup>
import { usePage, Link } from "@inertiajs/vue3";

const props = usePage().props.value;
const evaluation = props.evaluation;
</script>
