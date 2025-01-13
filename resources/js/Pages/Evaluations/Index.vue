<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">Liste des Évaluations</h1>

        <Link
            href="/evaluations/create"
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Nouvelle Évaluation
        </Link>

        <div class="mt-4">
            <table class="min-w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Module</th>
                        <th class="px-4 py-2">Score</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="eval in evaluations"
                        :key="eval.id"
                        class="border-b"
                    >
                        <td class="px-4 py-2">{{ eval.id }}</td>
                        <td class="px-4 py-2">
                            {{ eval.module ? eval.module.title : "N/A" }}
                        </td>
                        <td class="px-4 py-2">{{ eval.score }}</td>
                        <td class="px-4 py-2">
                            <Link
                                :href="`/evaluations/${eval.id}`"
                                class="text-blue-600 mr-2"
                                >Voir</Link
                            >
                            <Link
                                :href="`/evaluations/${eval.id}/edit`"
                                class="text-green-600 mr-2"
                                >Modifier</Link
                            >
                            <Link
                                as="button"
                                method="delete"
                                :href="`/evaluations/${eval.id}`"
                                class="text-red-600"
                            >
                                Supprimer
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";

const props = usePage().props.value;
const evaluations = props.evaluations;
</script>
