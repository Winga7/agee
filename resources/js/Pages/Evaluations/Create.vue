<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">Nouvelle Évaluation</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block font-medium mb-1">Module</label>
                <select v-model="form.module_id" class="border p-2 w-full">
                    <option value="">-- Choisir un module --</option>
                    <option
                        v-for="mod in modules"
                        :key="mod.id"
                        :value="mod.id"
                    >
                        {{ mod.title }}
                    </option>
                </select>
                <span v-if="errors.module_id" class="text-red-500">{{
                    errors.module_id
                }}</span>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Score (1 à 5)</label>
                <input
                    type="number"
                    v-model.number="form.score"
                    class="border p-2 w-full"
                    min="1"
                    max="5"
                />
                <span v-if="errors.score" class="text-red-500">{{
                    errors.score
                }}</span>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Commentaire</label>
                <textarea
                    v-model="form.comment"
                    class="border p-2 w-full"
                    rows="3"
                ></textarea>
                <span v-if="errors.comment" class="text-red-500">{{
                    errors.comment
                }}</span>
            </div>

            <button
                type="submit"
                class="bg-green-500 text-white px-4 py-2 rounded"
            >
                Enregistrer
            </button>
            <Link href="/evaluations" class="ml-2 underline text-blue-600"
                >Annuler</Link
            >
        </form>
    </div>
</template>

<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";

const props = usePage().props.value;
const modules = props.modules;

const form = useForm({
    module_id: "",
    score: "",
    comment: "",
});

function submit() {
    form.post("/evaluations", {
        onError: () => {},
    });
}

const errors = form.errors;
</script>
