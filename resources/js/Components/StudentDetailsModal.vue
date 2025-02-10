<script setup>
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

defineProps({
  student: {
    type: Object,
    required: true,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["close"]);
</script>

<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">
        Détails de l'étudiant
      </h3>

      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500">Nom</p>
            <p class="mt-1">{{ student.last_name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Prénom</p>
            <p class="mt-1">{{ student.first_name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Email</p>
            <p class="mt-1">{{ student.email }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Email scolaire</p>
            <p class="mt-1">{{ student.school_email || "-" }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Téléphone</p>
            <p class="mt-1">{{ student.telephone || "-" }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Numéro d'étudiant</p>
            <p class="mt-1">{{ student.student_number }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Date de naissance</p>
            <p class="mt-1">
              {{ new Date(student.birth_date).toLocaleDateString() }}
            </p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Classe</p>
            <p class="mt-1">{{ student.class?.name || "-" }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Année académique</p>
            <p class="mt-1">{{ student.academic_year }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Statut</p>
            <p class="mt-1">{{ student.status }}</p>
          </div>
        </div>

        <div class="mt-6">
          <h4 class="font-medium text-gray-900 mb-2">Inscriptions aux cours</h4>
          <div v-if="student.courseEnrollments?.length" class="space-y-2">
            <div
              v-for="enrollment in student.courseEnrollments"
              :key="enrollment.id"
              class="p-2 bg-gray-50 rounded"
            >
              {{ enrollment.module.title }}
              ({{ new Date(enrollment.start_date).toLocaleDateString() }} -
              {{ new Date(enrollment.end_date).toLocaleDateString() }})
            </div>
          </div>
          <p v-else class="text-gray-500">Aucune inscription aux cours</p>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="$emit('close')"> Fermer </SecondaryButton>
      </div>
    </div>
  </Modal>
</template>
