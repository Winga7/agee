<script setup>
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  modules: {
    type: Array,
    required: true,
  },
  sentTokens: {
    type: Array,
    required: true,
    default: () => [],
  },
  forms: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const selectedModule = ref("");
const selectedGroup = ref("");
const selectedForm = ref("");
const groups = ref([]);
const students = ref([]);

// Charger les groupes quand un module est sélectionné
const loadGroups = async () => {
  if (!selectedModule.value) {
    groups.value = [];
    students.value = [];
    return;
  }

  try {
    const response = await fetch(
      `/api/modules/${selectedModule.value}/groups`,
      {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
      }
    );

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(
        errorData.error || `HTTP error! status: ${response.status}`
      );
    }

    const data = await response.json();

    if (data.groups && Array.isArray(data.groups)) {
      groups.value = data.groups;
    }
    if (data.students) {
      students.value = data.students;
    }
  } catch (error) {
    axios.post("/api/log", {
      message: "Erreur lors du chargement des groupes",
      data: {
        error: error.message,
        moduleId: selectedModule.value,
      },
      level: "error",
    });
    groups.value = [];
    students.value = [];
  }
};

// Envoyer les invitations
const sendInvitations = () => {
  const form = useForm({
    module_id: selectedModule.value,
    class_group: selectedGroup.value,
    form_id: selectedForm.value,
  });

  form.post("/evaluations/generate-tokens", {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ["sentTokens"] });
      selectedModule.value = "";
      selectedGroup.value = "";
      selectedForm.value = "";
      groups.value = [];
      students.value = [];
    },
    onError: (errors) => {
      axios.post("/api/log", {
        message: "Erreur lors de l'envoi des invitations",
        data: { errors },
        level: "error",
      });
    },
  });
};

// Filtrer les étudiants par groupe sélectionné
const filteredStudents = computed(() => {
  if (!selectedGroup.value || !students.value[selectedGroup.value]) {
    return [];
  }
  return students.value[selectedGroup.value];
});

// Formater la date pour l'affichage
const formatDate = (date) => {
  return new Date(date).toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Ajout d'une computed property pour regrouper les tokens
const groupedTokens = computed(() => {
  const grouped = {};

  props.sentTokens.forEach((token) => {
    const key = `${token.module_id}_${token.class_id}`;

    if (!grouped[key]) {
      grouped[key] = {
        module: token.module,
        class: token.class,
        created_at: token.created_at,
        module_id: token.module_id,
        class_id: token.class_id,
        completed: token.completed || 0, // Utiliser completed au lieu de is_used
        total_sent: token.total_sent || 0,
        expired: token.is_expired || 0,
      };
    } else {
      // Mettre à jour les compteurs
      grouped[key].completed += token.completed || 0;
      grouped[key].total_sent += token.total_sent || 0;
      grouped[key].expired += token.is_expired || 0;
    }
  });

  return Object.values(grouped);
});

const handleError = (error, context) => {
  axios.post("/api/log", {
    message: `Erreur dans la gestion des évaluations: ${context}`,
    data: { error: error.message },
    level: "error",
  });
};
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
              <label class="block font-medium text-sm text-gray-700"
                >Module</label
              >
              <select
                v-model="selectedModule"
                @change="loadGroups"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              >
                <option value="">Sélectionner un module</option>
                <option
                  v-for="module in modules"
                  :key="module.id"
                  :value="module.id"
                >
                  {{ module.title }}
                </option>
              </select>
            </div>

            <!-- Sélection du groupe -->
            <div v-if="groups.length" class="mb-6">
              <label class="block font-medium text-sm text-gray-700"
                >Classe</label
              >
              <select
                v-model="selectedGroup"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              >
                <option value="">Sélectionner une classe</option>
                <option v-for="group in groups" :key="group" :value="group">
                  {{ group }}
                </option>
              </select>
            </div>

            <!-- Sélection du formulaire -->
            <div v-if="selectedModule && selectedGroup" class="mb-6">
              <label class="block font-medium text-sm text-gray-700">
                Formulaire d'évaluation
              </label>
              <select
                v-model="selectedForm"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                required
              >
                <option value="">Sélectionner un formulaire</option>
                <option v-for="form in forms" :key="form.id" :value="form.id">
                  {{ form.title }}
                </option>
              </select>
            </div>

            <!-- Bouton d'envoi -->
            <div
              v-if="selectedModule && selectedGroup && selectedForm"
              class="mt-6"
            >
              <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
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
                  <th class="px-6 py-3 bg-gray-50 text-left">Réponses</th>
                  <th class="px-6 py-3 bg-gray-50 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="group in groupedTokens"
                  :key="`${group.module_id}_${group.class_id}`"
                >
                  <td class="px-6 py-4">{{ group.module.title }}</td>
                  <td class="px-6 py-4">{{ group.class.name }}</td>
                  <td class="px-6 py-4">{{ formatDate(group.created_at) }}</td>
                  <td class="px-6 py-4">
                    <span
                      :class="{
                        'text-green-600':
                          group.completed === group.total_sent &&
                          group.completed > 0,
                        'text-red-600':
                          group.expired > 0 &&
                          group.completed < group.total_sent,
                        'text-yellow-600':
                          group.completed < group.total_sent &&
                          group.expired === 0,
                      }"
                    >
                      {{ group.completed }}/{{ group.total_sent }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <Link
                      v-if="group.completed > 0"
                      :href="`/evaluations/responses/${group.module_id}/${group.class_id}/${group.created_at}`"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      Voir les réponses
                    </Link>
                    <span v-else class="text-gray-400"> Aucune réponse </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Liste des étudiants -->
          <div v-if="selectedGroup && filteredStudents.length" class="mt-6">
            <h3 class="font-bold mb-2">
              Étudiants du groupe {{ selectedGroup }}
            </h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-6 py-3 bg-gray-50 text-left">Nom</th>
                  <th class="px-6 py-3 bg-gray-50 text-left">Prénom</th>
                  <th class="px-6 py-3 bg-gray-50 text-left">Email</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="enrollment in filteredStudents"
                  :key="enrollment.student.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4">{{ enrollment.student.last_name }}</td>
                  <td class="px-6 py-4">{{ enrollment.student.first_name }}</td>
                  <td class="px-6 py-4">
                    {{
                      enrollment.student.school_email ||
                      enrollment.student.email
                    }}
                    <span
                      v-if="enrollment.student.school_email"
                      class="text-xs text-gray-500 ml-2"
                      >(School)</span
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
