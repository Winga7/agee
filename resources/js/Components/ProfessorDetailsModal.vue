<script setup>
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

defineProps({
  professor: {
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
        Détails du professeur
      </h3>

      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500">Nom</p>
            <p class="mt-1">{{ professor.last_name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Prénom</p>
            <p class="mt-1">{{ professor.first_name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Email</p>
            <p class="mt-1">{{ professor.email }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Email professionnel</p>
            <p class="mt-1">{{ professor.school_email || "-" }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Téléphone</p>
            <p class="mt-1">{{ professor.telephone || "-" }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Date de naissance</p>
            <p class="mt-1">
              {{
                professor.birth_date
                  ? new Date(professor.birth_date).toLocaleDateString()
                  : "-"
              }}
            </p>
          </div>
          <div class="col-span-2">
            <p class="text-sm font-medium text-gray-500">Adresse</p>
            <p class="mt-1">{{ professor.adress || "-" }}</p>
          </div>
        </div>

        <div class="mt-6">
          <h4 class="font-medium text-gray-900 mb-2">Modules enseignés</h4>
          <div v-if="professor.modules?.length" class="space-y-2">
            <div
              v-for="module in professor.modules"
              :key="module.id"
              class="p-2 bg-gray-50 rounded"
            >
              <div class="flex justify-between items-center">
                <span>{{ module.title }}</span>
                <span class="text-sm text-gray-500">{{
                  module.code || "-"
                }}</span>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500">Aucun module assigné</p>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="$emit('close')">Fermer</SecondaryButton>
      </div>
    </div>
  </Modal>
</template>
