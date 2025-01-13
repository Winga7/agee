<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">Créer un Module</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block font-medium mb-1">Titre</label>
                <input
                    type="text"
                    v-model="form.title"
                    class="border p-2 w-full"
                />
                <span v-if="errors.title" class="text-red-500">{{
                    errors.title
                }}</span>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Code</label>
                <input
                    type="text"
                    v-model="form.code"
                    class="border p-2 w-full"
                />
                <span v-if="errors.code" class="text-red-500">{{
                    errors.code
                }}</span>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Professeur</label>
                <select v-model="form.professor_id" class="border p-2 w-full">
                    <option :value="null">-- Aucun --</option>
                    <option
                        v-for="prof in professors"
                        :value="prof.id"
                        :key="prof.id"
                    >
                        {{ prof.first_name }} {{ prof.last_name }}
                    </option>
                </select>
                <span v-if="errors.professor_id" class="text-red-500">{{
                    errors.professor_id
                }}</span>
            </div>

            <button
                type="submit"
                class="bg-green-500 text-white px-4 py-2 rounded"
            >
                Enregistrer
            </button>
            <Link href="/modules" class="ml-2 underline text-blue-600"
                >Annuler</Link
            >
        </form>
    </div>
</template>

<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";

const props = usePage().props.value;
const professors = props.professors;

// useForm nous permet de gérer plus facilement l'envoi de data
const form = useForm({
    title: "",
    code: "",
    professor_id: null,
});

function submit() {
    form.post("/modules", {
        onError: () => {
            // Les erreurs de validation sont déjà gérées par Inertia
        },
    });
}

const errors = form.errors;
</script>
