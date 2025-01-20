<template>
    <AppLayout title="Gestion des Évaluations">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestion des Envois d'Évaluations
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Sélection du module -->
                    <div class="mb-6">
                        <label class="block font-medium text-sm text-gray-700">Module</label>
                        <select v-model="selectedModule" @change="loadGroups" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Sélectionner un module</option>
                            <option v-for="module in modules" :key="module.id" :value="module.id">
                                {{ module.title }}
                            </option>
                        </select>
                    </div>

                    <!-- Liste des groupes -->
                    <div v-if="groups.length" class="mb-6">
                        <h3 class="font-bold mb-2">Groupes disponibles</h3>
                        <div class="space-y-2">
                            <div v-for="group in groups" :key="group" class="flex items-center justify-between p-3 border rounded">
                                <div>
                                    <span class="font-medium">{{ group }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ getStudentCount(group) }} étudiants)</span>
                                </div>
                                <button
                                    @click="sendEvaluations(group)"
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                                >
                                    Envoyer les évaluations
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Historique des envois -->
                    <div v-if="sentTokens.length" class="mt-8">
                        <h3 class="font-bold mb-2">Historique des envois récents</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Groupe</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'envoi</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="token in sentTokens" :key="token.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ token.module.title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ token.class_group }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(token.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(token)">
                                                {{ token.is_used ? 'Utilisé' : 'En attente' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const selectedModule = ref('');
const groups = ref([]);
const sentTokens = ref([]);
const modules = ref([]);

// Charger les groupes quand un module est sélectionné
async function loadGroups() {
    if (!selectedModule.value) return;

    const response = await axios.get(`/api/modules/${selectedModule.value}/groups`);
    groups.value = response.data;
}

// Envoyer les évaluations pour un groupe
function sendEvaluations(group) {
    useForm().post(`/evaluations/generate-tokens/${selectedModule.value}`, {
        class_group: group
    }, {
        onSuccess: () => {
            // Recharger l'historique
            loadSentTokens();
        }
    });
}

// Formater la date
function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR');
}

// Charger l'historique des tokens envoyés
async function loadSentTokens() {
    const response = await axios.get('/api/evaluation-tokens/recent');
    sentTokens.value = response.data;
}

function getStatusClass(token) {
    return {
        'px-2 py-1 rounded text-sm': true,
        'bg-green-100 text-green-800': token.is_used,
        'bg-yellow-100 text-yellow-800': !token.is_used
    }
}

onMounted(() => {
    // Charger les modules au montage du composant
    axios.get('/api/modules').then(response => {
        modules.value = response.data;
    });
});
</script>
