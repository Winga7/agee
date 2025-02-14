<script setup>
import { ref } from "vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  show: Boolean,
  student: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "refresh"]);

const deleteEnrollment = (enrollment) => {
  if (confirm("Êtes-vous sûr de vouloir supprimer cette inscription ?")) {
    router.delete(route("course-enrollments.destroy", enrollment.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit("refresh");
      },
      onError: (errors) => {
        alert("Erreur lors de la suppression de l'inscription");
      },
    });
  }
};

// Fonctions de formatage existantes
const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("fr-BE");
};

const formatPhoneNumber = (phoneNumber) => {
  if (!phoneNumber) return "";
  const formattedNumber = phoneNumber.replace(/\D/g, "");
  const match = formattedNumber.match(/^(\d{2})(\d{2})(\d{2})(\d{2})$/);
  if (match) {
    return `(${match[1]}) ${match[2]}-${match[3]}-${match[4]}`;
  }
  return phoneNumber;
};
</script>

<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="p-6">
      <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">
        {{ student?.first_name }} {{ student?.last_name }}
      </h2>

      <!-- Informations de base -->
      <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-6">
          <!-- Colonne gauche -->
          <div class="space-y-4">
            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Email personnel</span>
              <span class="text-gray-700 font-medium">{{
                student?.email
              }}</span>
            </div>

            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Email scolaire</span>
              <span class="text-gray-700 font-medium">{{
                student?.school_email
              }}</span>
            </div>

            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Téléphone</span>
              <span class="text-gray-700 font-medium">{{
                formatPhoneNumber(student?.telephone)
              }}</span>
            </div>
          </div>

          <!-- Colonne droite -->
          <div class="space-y-4">
            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Date de naissance</span>
              <span class="text-gray-700 font-medium">{{
                formatDate(student?.birth_date)
              }}</span>
            </div>

            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Numéro d'étudiant</span>
              <span class="text-gray-700 font-medium">{{
                student?.student_number
              }}</span>
            </div>

            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Année académique</span>
              <span class="text-gray-700 font-medium">{{
                student?.academic_year
              }}</span>
            </div>

            <div class="flex flex-col">
              <span class="text-sm text-gray-500">Statut</span>
              <span class="inline-flex items-center">
                <span
                  class="px-2 py-1 text-sm rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': student?.status === 'active',
                    'bg-red-100 text-red-800': student?.status === 'inactive',
                    'bg-blue-100 text-blue-800':
                      student?.status === 'graduated',
                  }"
                >
                  {{ student?.status }}
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Classes -->
      <div class="mt-6">
        <h3 class="text-md font-medium text-gray-900 mb-2">Classes</h3>
        <div v-if="student?.classes?.length" class="space-y-2">
          <div
            v-for="class_group in student.classes"
            :key="class_group.id"
            class="flex items-center justify-between bg-gray-50 p-2 rounded"
          >
            <span>{{ class_group.name }}</span>
          </div>
        </div>
        <p v-else class="text-gray-500">Aucune classe assignée</p>
      </div>

      <!-- Inscriptions aux modules -->
      <div class="mt-6">
        <h3 class="text-md font-medium text-gray-900 mb-2">Modules</h3>
        <div v-if="student?.courseEnrollments?.length" class="mt-4">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Module
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Classe
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Période
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="enrollment in student.courseEnrollments"
                :key="enrollment.id"
              >
                <td class="px-6 py-4">{{ enrollment.module?.title }}</td>
                <td class="px-6 py-4">
                  {{
                    student.classes.find((c) => c.id === enrollment.class_id)
                      ?.name
                  }}
                </td>
                <td class="px-6 py-4">
                  {{ formatDate(enrollment.start_date) }} -
                  {{ formatDate(enrollment.end_date) }}
                </td>
                <td class="px-6 py-4">
                  <button
                    @click.prevent="deleteEnrollment(enrollment)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-gray-500">Aucun module assigné</p>
      </div>

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="$emit('close')">Fermer</SecondaryButton>
      </div>
    </div>
  </Modal>
</template>
