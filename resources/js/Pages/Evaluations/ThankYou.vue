<script setup>
import PublicLayout from "@/Layouts/PublicLayout.vue";
</script>

<template>
  <PublicLayout title="Merci pour votre évaluation">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white p-6 shadow-sm rounded-lg">
        <div class="text-center py-8">
          <svg
            class="mx-auto h-12 w-12 text-green-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
          <h2 class="mt-4 text-2xl font-bold text-gray-900">
            Merci pour votre évaluation !
          </h2>
          <p class="mt-2 text-gray-600">
            Votre retour est précieux et nous aidera à améliorer la qualité de
            nos formations.
          </p>
          <p class="text-sm text-gray-500 mt-2">
            Vous pouvez maintenant fermer cette fenêtre.
          </p>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
