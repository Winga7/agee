<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

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
</script>

<template>
  <AppLayout :title="module.title">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- En-tête -->
          <div class="mb-6">
            <h2 class="text-2xl font-bold mb-2">
              {{ module.title }}
            </h2>
            <p class="text-gray-600">
              Classe : {{ classGroup.name }} <br />
              Date d'envoi : {{ formatDate(date) }}
            </p>
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
                    Email
                  </th>
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
                <tr v-for="token in tokens" :key="token.student_email">
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ token.student_email }}
                  </td>
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
                    <div v-if="token.answers" class="space-y-2">
                      <div
                        v-for="(answer, question) in token.answers"
                        :key="question"
                      >
                        <div class="font-semibold">
                          Question {{ question }}:
                        </div>
                        <div class="text-gray-600">{{ answer }}</div>
                      </div>
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
  </AppLayout>
</template>
