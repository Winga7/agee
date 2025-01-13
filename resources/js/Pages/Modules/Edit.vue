<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">
            Modifier le Module #{{ module.id }}
        </h1>

        <form @submit.prevent="updateModule">
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
                Mettre à jour
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
const module = props.module;
const professors = props.professors;

const form = useForm({
    title: module.title || "",
    code: module.code || "",
    professor_id: module.professor_id || null,
});

function updateModule() {
    form.put(`/modules/${module.id}`, {
        onError: () => {},
    });
}

const errors = form.errors;
</script>
