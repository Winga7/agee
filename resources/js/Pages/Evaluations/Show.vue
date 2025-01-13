<template>
    <AppLayout title="Détails de l'évaluation">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Évaluation du module {{ evaluation.module.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-bold text-lg">Module</h3>
                        <p>{{ evaluation.module.title }}</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-bold text-lg">Note</h3>
                        <p>{{ evaluation.score }}/5</p>
                    </div>

                    <div class="mb-4" v-if="evaluation.anonymized_comment">
                        <h3 class="font-bold text-lg">Commentaire</h3>
                        <p class="whitespace-pre-line">{{ evaluation.anonymized_comment }}</p>
                    </div>

                    <Link
                        :href="route('evaluations.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Retour à la liste
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    evaluation: {
        type: Object,
        required: true
    }
});
</script>
