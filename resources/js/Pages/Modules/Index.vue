<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">Liste des Modules</h1>

        <Link
            href="/modules/create"
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Créer un module
        </Link>

        <div class="mt-4">
            <table class="min-w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Titre</th>
                        <th class="px-4 py-2 text-left">Professeur</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="mod in modules" :key="mod.id" class="border-b">
                        <td class="px-4 py-2">{{ mod.id }}</td>
                        <td class="px-4 py-2">
                            <Link
                                :href="`/modules/${mod.id}`"
                                class="text-blue-600 hover:underline"
                            >
                                {{ mod.title }}
                            </Link>
                        </td>
                        <td class="px-4 py-2">
                            {{
                                mod.professor
                                    ? mod.professor.first_name +
                                      " " +
                                      mod.professor.last_name
                                    : "N/A"
                            }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <Link
                                class="text-blue-600 mr-2"
                                :href="`/modules/${mod.id}/edit`"
                                >Modifier</Link
                            >
                            <Link
                                as="button"
                                method="delete"
                                :href="`/modules/${mod.id}`"
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
// On récupère les données passées par le controller
const modules = props.modules;
</script>
