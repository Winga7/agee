<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  tokens: {
    type: Array,
    required: true,
  },
  module: {
    type: Object,
    required: true,
  },
  classGroup: {
    type: Object,
    required: true,
  },
  date: {
    type: String,
    required: true,
  },
  questions: {
    type: Object,
    required: true,
  },
});

// Ajout de logs pour vérifier les données
console.log("Tokens reçus:", props.tokens);

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Statistiques plus détaillées
const totalResponses = computed(() => {
  console.log("Total tokens:", props.tokens.length);
  return props.tokens.length;
});

const completedResponses = computed(() => {
  const completed = props.tokens.filter(
    (token) => token.used_at !== null
  ).length;
  console.log("Réponses complétées:", completed);
  return completed;
});

const pendingResponses = computed(() => {
  const pending = props.tokens.filter((token) => token.used_at === null).length;
  console.log("Réponses en attente:", pending);
  return pending;
});

// Ajout de logs plus détaillés
const tokensWithAnswers = computed(() => {
  console.log(
    "Tokens avec réponses:",
    props.tokens.filter((t) => t.answers)
  );
  return props.tokens.filter((t) => t.answers);
});

// Ajout des états pour le modal
const isModalOpen = ref(false);
const selectedResponse = ref(null);

// Fonction pour ouvrir le modal
const openResponseModal = (token) => {
  selectedResponse.value = token;
  isModalOpen.value = true;
};

// Fonction pour fermer le modal
const closeModal = () => {
  selectedResponse.value = null;
  isModalOpen.value = false;
};

// Supprimer downloadUrl computed car on va utiliser une fonction à la place
const downloadExcel = async () => {
  try {
    const response = await axios.get(
      route("evaluations.download", {
        module: props.module.id,
        class: props.classGroup.id,
        date: props.date,
      }),
      { responseType: "blob" } // Important pour les fichiers
    );

    // Créer un lien temporaire pour le téléchargement
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute(
      "download",
      `evaluations-${props.module.title}-${formatDate(props.date)}.xlsx`
    );
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Erreur lors du téléchargement:", error);
    alert("Une erreur est survenue lors du téléchargement du fichier Excel");
  }
};
</script>

<template>
  <AppLayout :title="module.title">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- En-tête avec bouton de téléchargement -->
          <div class="mb-6 flex justify-between items-start">
            <div>
              <h2 class="text-2xl font-bold mb-2">
                {{ module.title }}
              </h2>
              <p class="text-gray-600">
                Classe : {{ classGroup.name }} <br />
                Date d'envoi : {{ formatDate(date) }}
              </p>
            </div>
            <button
              @click="downloadExcel"
              class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-700 rounded-md shadow-sm transition-colors duration-200 font-medium"
            >
              <svg
                class="w-5 h-5 mr-2"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 48 48"
              >
                <path
                  fill="#185ABD"
                  d="M41,10H25V7c0-1.105-0.895-2-2-2H7C5.895,5,5,5.895,5,7v34c0,1.105,0.895,2,2,2h34c1.105,0,2-0.895,2-2V12C43,10.895,42.105,10,41,10z"
                />
                <path
                  fill="#21A366"
                  d="M41,10H25v27h16c1.105,0,2-0.895,2-2V12C43,10.895,42.105,10,41,10z"
                />
                <path fill="#107C41" d="M25,10h16v27H25V10z" />
                <path
                  fill="#000000"
                  opacity=".1"
                  d="M30.31,23.48l-2.39-2.39l-3.59,3.59l-2.39-2.39l-3.59,3.59V31h15.96v-4.52L30.31,23.48z"
                />
                <path
                  fill="#FFFFFF"
                  d="M19.32,30.68h-3.55l4.42-6.88l-4.06-6.28h3.63l2.45,3.93l2.39-3.93h3.51l-4.05,6.54l4.46,6.62h-3.74l-2.77-4.26L19.32,30.68z"
                />
              </svg>
              Télécharger Excel
            </button>
          </div>

          <!-- Statistiques -->
          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded">
              <div class="text-lg font-semibold">Total envoyé</div>
              <div class="text-2xl">{{ totalResponses }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded">
              <div class="text-lg font-semibold">Réponses reçues</div>
              <div class="text-2xl text-green-600">
                {{ completedResponses }}
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded">
              <div class="text-lg font-semibold">En attente/Expirés</div>
              <div class="text-2xl text-red-600">
                {{ pendingResponses }}
              </div>
            </div>
          </div>

          <!-- Tableau des réponses -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th
                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Statut
                  </th>
                  <th
                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Date de réponse
                  </th>
                  <th
                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Réponses
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="token in tokens" :key="token.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="{
                        'px-2 py-1 rounded text-sm font-semibold': true,
                        'bg-green-100 text-green-800': token.used_at != null,
                        'bg-yellow-100 text-yellow-800':
                          token.used_at == null && !token.isExpired,
                        'bg-red-100 text-red-800':
                          token.used_at == null && token.isExpired,
                      }"
                    >
                      {{
                        token.used_at
                          ? "Complété"
                          : token.isExpired
                          ? "Expiré"
                          : "En attente"
                      }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ token.used_at ? formatDate(token.used_at) : "-" }}
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="token.answers">
                      <button
                        @click="openResponseModal(token)"
                        class="text-blue-600 hover:text-blue-800 font-medium"
                      >
                        Voir les réponses
                      </button>
                    </div>
                    <span v-else class="text-gray-400">Pas de réponse</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal des réponses -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                Réponses détaillées
              </h3>
              <p class="text-sm text-gray-500 mt-1">
                Répondu le
                {{
                  selectedResponse?.used_at
                    ? formatDate(selectedResponse.used_at)
                    : "-"
                }}
              </p>
            </div>
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-500"
            >
              <svg
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>

        <div class="px-6 py-4">
          <div v-if="selectedResponse?.answers" class="space-y-6">
            <div
              v-for="(answer, questionId) in selectedResponse.answers"
              :key="questionId"
              class="border-b border-gray-200 last:border-0 pb-4 last:pb-0"
            >
              <h4 class="font-medium text-gray-900 mb-2">
                {{ answer.question }}
              </h4>
              <p class="text-gray-700 whitespace-pre-wrap">
                {{ answer.answer }}
              </p>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <div class="flex justify-end">
            <button
              @click="closeModal"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 font-medium"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
