<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">
            Détails du Module #{{ module.id }}
        </h1>

        <p><strong>Titre :</strong> {{ module.title }}</p>
        <p><strong>Code :</strong> {{ module.code }}</p>
        <p>
            <strong>Professeur :</strong>
            {{
                module.professor
                    ? module.professor.first_name +
                      " " +
                      module.professor.last_name
                    : "N/A"
            }}
        </p>

        <Link href="/modules" class="underline text-blue-600 mt-4 inline-block">
            Retour à la liste
        </Link>
    </div>
</template>

<script setup>
import { usePage, Link } from "@inertiajs/vue3";

const props = usePage().props.value;
const module = props.module;
</script>
