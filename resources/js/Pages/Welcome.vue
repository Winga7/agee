<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
});
</script>

<template>
  <Head title="Accueil" />
  <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 min-h-screen">
    <!-- Header/Navigation -->
    <header class="relative z-10 px-6 py-4">
      <nav class="max-w-7xl mx-auto flex justify-end">
        <div v-if="canLogin" class="space-x-4">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="text-white hover:text-indigo-200 transition"
          >
            Tableau de bord
          </Link>

          <template v-else>
            <Link
              :href="route('login')"
              class="text-white hover:text-indigo-200 transition"
            >
              Connexion
            </Link>
          </template>
        </div>
      </nav>
    </header>

    <!-- Hero Section -->
    <div class="relative px-6 lg:px-8">
      <div class="mx-auto max-w-7xl py-24 sm:py-32">
        <div class="text-center">
          <img
            src="/images/logo-agee.png"
            alt="logo AGEE"
            class="mx-auto h-24 sm:h-32 md:h-40 transform transition-transform duration-700 hover:scale-105"
          />
          <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">
            Évaluations des Enseignements
          </h1>
          <p class="mt-6 text-lg leading-8 text-indigo-100">
            Participez à l'amélioration continue de la qualité de l'enseignement
            à travers vos retours constructifs.
          </p>
        </div>
      </div>
    </div>

    <!-- Features Grid -->
    <div class="relative">
      <!-- Vague décorative -->
      <div class="absolute top-0 left-0 right-0 w-full overflow-hidden">
        <svg class="relative block w-full h-[70px]" viewBox="0 0 1440 74">
          <path
            class="fill-white"
            d="M0,32L60,37.3C120,43,240,53,360,58.7C480,64,600,64,720,58.7C840,53,960,43,1080,42.7C1200,43,1320,53,1380,58.7L1440,64L1440,74L1380,74C1320,74,1200,74,1080,74C960,74,840,74,720,74C600,74,480,74,360,74C240,74,120,74,60,74L0,74Z"
          ></path>
        </svg>
      </div>

      <!-- Features Content -->
      <div class="bg-white pt-16 pb-24 relative">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <!-- Feature 1 -->
            <div
              class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition"
            >
              <div
                class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-600 text-white mb-6"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Anonymat Total
              </h3>
              <p class="text-gray-600">
                Exprimez-vous en toute confiance. Votre identité reste protégée
                tout au long du processus d'évaluation.
              </p>
            </div>

            <!-- Feature 2 -->
            <div
              class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition"
            >
              <div
                class="flex items-center justify-center h-12 w-12 rounded-md bg-green-600 text-white mb-6"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Processus Simple
              </h3>
              <p class="text-gray-600">
                Une interface intuitive pour évaluer vos cours en quelques
                clics. Rapide, efficace et accessible.
              </p>
            </div>

            <!-- Feature 3 -->
            <div
              class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition"
            >
              <div
                class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white mb-6"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Impact Direct
              </h3>
              <p class="text-gray-600">
                Vos retours contribuent directement à l'amélioration des
                enseignements et de votre expérience d'apprentissage.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
