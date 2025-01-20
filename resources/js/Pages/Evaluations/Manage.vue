<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    modules: {
        type: Array,
        required: true
    },
    sentTokens: {
        type: Array,
        required: true,
        default: () => []
    }
})

console.log('Props reçues:', props.sentTokens)

const selectedModule = ref('')
const selectedGroup = ref('')
const groups = ref([])
const students = ref([])

// Charger les groupes quand un module est sélectionné
const loadGroups = async () => {
    if (!selectedModule.value) {
        groups.value = []
        students.value = []
        return
    }

    try {
        console.log('Chargement des groupes pour le module:', selectedModule.value)
        const response = await fetch(`/api/modules/${selectedModule.value}/groups`)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        const data = await response.json()
        console.log('Données reçues:', data)
        groups.value = data.groups
        students.value = data.students
    } catch (error) {
        console.error('Erreur lors du chargement des groupes:', error)
        groups.value = []
        students.value = []
    }
}

// Filtrer les étudiants par groupe sélectionné
const filteredStudents = computed(() => {
    if (!selectedGroup.value || !students.value[selectedGroup.value]) return []
    return students.value[selectedGroup.value]
})

// Envoyer les invitations
const sendInvitations = () => {
    const form = useForm({
        module_id: selectedModule.value,
        class_group: selectedGroup.value
    });

    form.post('/evaluations/generate-tokens', {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['sentTokens'] });
            selectedModule.value = '';
            selectedGroup.value = '';
            groups.value = [];
            students.value = [];
        },
        onError: (errors) => {
            console.error('Erreurs:', errors);
        }
    });
}

// Formater la date pour l'affichage
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

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
                    <form @submit.prevent="sendInvitations">
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

                        <!-- Sélection du groupe -->
                        <div v-if="groups.length" class="mb-6">
                            <label class="block font-medium text-sm text-gray-700">Classe</label>
                            <select v-model="selectedGroup" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Sélectionner un groupe</option>
                                <option v-for="group in groups" :key="group" :value="group">
                                    {{ group }}
                                </option>
                            </select>
                        </div>

                        <!-- Bouton d'envoi -->
                        <div v-if="selectedModule && selectedGroup" class="mt-6">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Envoyer les invitations
                            </button>
                        </div>
                    </form>

                    <!-- Historique des envois -->
                    <div v-if="sentTokens && sentTokens.length" class="mt-8">
                        <h3 class="font-bold mb-2">Historique des envois récents</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Module</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Classe</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Date d'envoi</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="token in sentTokens" :key="token.id">
                                    <td class="px-6 py-4">{{ token.module.title }}</td>
                                    <td class="px-6 py-4">{{ token.class_group }}</td>
                                    <td class="px-6 py-4">{{ formatDate(token.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'text-green-600': token.completed + token.expired === token.total_sent,
                                            'text-yellow-600': token.pending > 0,
                                            'text-red-600': token.completed === 0 && token.expired === token.total_sent
                                        }">
                                            {{ token.completed + token.expired === token.total_sent ? 'Terminé' : 'En cours' }}
                                            <span class="text-gray-500 text-sm">
                                                ({{ token.completed }}/{{ token.total_sent }} réponses)
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Liste des étudiants -->
                    <div v-if="selectedGroup && filteredStudents.length" class="mt-6">
                        <h3 class="font-bold mb-2">Étudiants du groupe {{ selectedGroup }}</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Nom</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Prénom</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="enrollment in filteredStudents" :key="enrollment.student.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ enrollment.student.last_name }}</td>
                                    <td class="px-6 py-4">{{ enrollment.student.first_name }}</td>
                                    <td class="px-6 py-4">{{ enrollment.student.email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
