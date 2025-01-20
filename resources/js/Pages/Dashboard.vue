<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Welcome from "@/Components/Welcome.vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            totalModules: 0,
            totalEvaluations: 0,
            averageScore: 0,
            pendingEvaluations: 0,
            completedEvaluations: 0,
            participationRate: 0,
        }),
    },
    modules: {
        type: Array,
        default: () => [],
    },
    years: {
        type: Array,
        default: () => [],
    },
    currentAcademicYear: {
        type: Number,
        required: true,
    },
});

const selectedYear = ref(props.currentAcademicYear);
const selectedModule = ref("all");
const filteredStats = ref(props.stats);

// Fonction pour mettre à jour les statistiques en fonction des filtres
function updateStats() {
    router.get(
        "/dashboard",
        {
            year: selectedYear.value,
            module_id:
                selectedModule.value === "all" ? null : selectedModule.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ["stats"],
            onSuccess: (page) => {
                filteredStats.value = page.props.stats;
            },
        }
    );
}

watch([selectedYear, selectedModule], () => {
    updateStats();
});
</script>

<template>
    <AppLayout title="Tableau de Bord">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tableau de Bord
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Année Académique
                            </label>
                            <select
                                v-model="selectedYear"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                            >
                                <option
                                    v-for="year in years"
                                    :key="year"
                                    :value="year"
                                >
                                    {{ year }}-{{ year + 1 }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Module
                            </label>
                            <select
                                v-model="selectedModule"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                            >
                                <option value="all">Tous les modules</option>
                                <option
                                    v-for="module in modules"
                                    :key="module.id"
                                    :value="module.id"
                                >
                                    {{ module.title }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-indigo-600 bg-opacity-75"
                            >
                                <svg
                                    class="h-8 w-8 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <div class="text-gray-400">Modules Évalués</div>
                                <div class="text-2xl font-semibold">
                                    {{ filteredStats.totalModules }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            Note moyenne : {{ filteredStats.averageScore }}/5
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-green-600 bg-opacity-75"
                            >
                                <svg
                                    class="h-8 w-8 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <div class="text-gray-400">
                                    Évaluations Complétées
                                </div>
                                <div class="text-2xl font-semibold">
                                    {{ filteredStats.completedEvaluations }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-yellow-600 bg-opacity-75"
                            >
                                <svg
                                    class="h-8 w-8 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <div class="text-gray-400">
                                    Taux de Participation
                                </div>
                                <div class="text-2xl font-semibold">
                                    {{ filteredStats.participationRate }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Welcome />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
