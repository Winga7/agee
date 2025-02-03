<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <div>
        <!-- Hero section -->
        <div
            class="relative overflow-hidden bg-gradient-to-br from-indigo-600 to-indigo-800"
        >
            <div class="p-8 lg:p-12">
                <div class="mx-auto max-w-7xl">
                    <div class="mt-12 text-center">
                        <h1
                            class="text-4xl font-bold tracking-tight text-white sm:text-5xl"
                        >
                            Évaluations des Enseignements
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-100">
                            Améliorons ensemble la qualité de nos formations à
                            travers vos retours constructifs.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Vague décorative -->
            <div
                class="absolute bottom-0 left-0 right-0 w-full overflow-hidden"
            >
                <svg
                    class="relative block w-[calc(100%+1.3px)] h-[70px]"
                    viewBox="0 0 1440 74"
                    preserveAspectRatio="none"
                >
                    <path
                        class="fill-white"
                        d="M0,32L60,37.3C120,43,240,53,360,58.7C480,64,600,64,720,58.7C840,53,960,43,1080,42.7C1200,43,1320,53,1380,58.7L1440,64L1440,74L1380,74C1320,74,1200,74,1080,74C960,74,840,74,720,74C600,74,480,74,360,74C240,74,120,74,60,74L0,74Z"
                    ></path>
                </svg>
            </div>
        </div>

        <!-- Features grid -->
        <div class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:gap-12">
                    <!-- Évaluations Anonymes -->
                    <div
                        class="rounded-xl bg-gray-50 p-8 shadow-sm ring-1 ring-gray-200 transition-all hover:shadow-md"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600"
                            >
                                <svg
                                    class="h-6 w-6 text-white"
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
                            <h2
                                class="ml-4 text-xl font-semibold text-gray-900"
                            >
                                Anonymat Garanti
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-600">
                            Exprimez-vous librement sur la qualité des
                            enseignements. Vos commentaires sont automatiquement
                            anonymisés pour garantir votre confidentialité.
                        </p>
                    </div>

                    <!-- Processus Simple -->
                    <div
                        class="rounded-xl bg-gray-50 p-8 shadow-sm ring-1 ring-gray-200 transition-all hover:shadow-md"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-600"
                            >
                                <svg
                                    class="h-6 w-6 text-white"
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
                            </div>
                            <h2
                                class="ml-4 text-xl font-semibold text-gray-900"
                            >
                                Évaluation Simplifiée
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-600">
                            Un processus en quelques clics : recevez votre
                            invitation, connectez-vous et évaluez rapidement vos
                            modules d'enseignement.
                        </p>
                    </div>

                    <!-- Amélioration Continue -->
                    <div
                        class="rounded-xl bg-gray-50 p-8 shadow-sm ring-1 ring-gray-200 transition-all hover:shadow-md"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-600"
                            >
                                <svg
                                    class="h-6 w-6 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    />
                                </svg>
                            </div>
                            <h2
                                class="ml-4 text-xl font-semibold text-gray-900"
                            >
                                Impact Concret
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-600">
                            Vos retours permettent d'améliorer concrètement la
                            qualité des formations et d'adapter les
                            enseignements aux besoins des étudiants.
                        </p>
                    </div>

                    <!-- Sécurité -->
                    <div
                        class="rounded-xl bg-gray-50 p-8 shadow-sm ring-1 ring-gray-200 transition-all hover:shadow-md"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-600"
                            >
                                <svg
                                    class="h-6 w-6 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    />
                                </svg>
                            </div>
                            <h2
                                class="ml-4 text-xl font-semibold text-gray-900"
                            >
                                Accès Sécurisé
                            </h2>
                        </div>

                        <p class="mt-4 text-gray-600">
                            Système d'invitation par token unique et accès
                            restreint aux étudiants inscrits pour garantir
                            l'intégrité des évaluations.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
