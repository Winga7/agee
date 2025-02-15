<script setup>
import { ref, computed, onMounted, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
  show: Boolean,
  form: Object,
});

const emit = defineEmits(["close"]);

const currentSection = ref(0);
const answers = ref({});

const currentSectionData = computed(() => {
  return props.form?.sections[currentSection.value] || null;
});

const isLastSection = computed(() => {
  return currentSection.value >= (props.form?.sections.length || 0) - 1;
});

const canProceed = computed(() => {
  if (!currentSectionData.value) return false;

  return currentSectionData.value.questions.every((question) => {
    if (!question.is_required) return true;
    if (question.type === "checkbox") {
      return answers.value[question.id]?.length > 0;
    }
    return !!answers.value[question.id];
  });
});

const nextSection = () => {
  if (!canProceed.value || isLastSection.value) return;

  // Trouver la prochaine section visible
  let nextIndex = currentSection.value + 1;
  while (nextIndex < props.form.sections.length) {
    const section = props.form.sections[nextIndex];

    // Si la section n'a pas de dépendance, elle est visible
    if (!section.depends_on_question_id) {
      currentSection.value = nextIndex;
      break;
    }

    // Vérifier la condition de visibilité
    let isVisible = false;
    props.form.sections.forEach((s) => {
      s.questions.forEach((q) => {
        if (q.id === section.depends_on_question_id) {
          isVisible = answers.value[q.id] === section.depends_on_answer;
        }
      });
    });

    if (isVisible) {
      currentSection.value = nextIndex;
      break;
    }

    // Si la section n'est pas visible, passer à la suivante
    nextIndex++;
  }
};

// Ajouter une nouvelle méthode previousSection
const previousSection = () => {
  if (currentSection.value <= 0) return;

  // Trouver la section précédente visible
  let prevIndex = currentSection.value - 1;
  while (prevIndex >= 0) {
    const section = props.form.sections[prevIndex];

    // Si la section n'a pas de dépendance, elle est visible
    if (!section.depends_on_question_id) {
      currentSection.value = prevIndex;
      break;
    }

    // Vérifier la condition de visibilité
    let isVisible = false;
    props.form.sections.forEach((s) => {
      s.questions.forEach((q) => {
        if (q.id === section.depends_on_question_id) {
          isVisible = answers.value[q.id] === section.depends_on_answer;
        }
      });
    });

    if (isVisible) {
      currentSection.value = prevIndex;
      break;
    }

    // Si la section n'est pas visible, passer à la précédente
    prevIndex--;
  }
};

const initializeAnswers = () => {
  if (!props.form || !props.form.sections) return;

  answers.value = {}; // Reset answers

  props.form.sections.forEach((section) => {
    section.questions.forEach((question) => {
      // Initialisation spécifique selon le type
      switch (question.type) {
        case "checkbox":
          answers.value[question.id] = [];
          break;
        case "radio":
          answers.value[question.id] = "";
          break;
        case "rating":
          answers.value[question.id] = 0;
          break;
        case "select":
          answers.value[question.id] = "";
          break;
        default:
          answers.value[question.id] = "";
      }
    });
  });
};

// Réinitialiser les réponses quand le formulaire change
watch(
  () => props.form,
  () => {
    initializeAnswers();
  },
  { deep: true }
);

// Réinitialiser les réponses au montage
onMounted(() => {
  initializeAnswers();
});

const resetPreview = () => {
  currentSection.value = 0;
  initializeAnswers();
};

const shouldShowSection = computed(() => {
  if (!currentSectionData.value) return false;
  if (!currentSectionData.value.depends_on_question_id) return true;

  // Rechercher la question contrôlant la visibilité
  let controllingQuestion = null;
  for (const section of props.form.sections) {
    for (const question of section.questions) {
      if (question.id === currentSectionData.value.depends_on_question_id) {
        controllingQuestion = question;
        break;
      }
    }
    if (controllingQuestion) break;
  }

  if (!controllingQuestion) return true;

  // Vérifier si la réponse correspond
  return (
    answers.value[controllingQuestion.id] ===
    currentSectionData.value.depends_on_answer
  );
});
</script>

<template>
  <Modal :show="show" @close="$emit('close')" maxWidth="2xl">
    <!-- Vérifier que form existe avant d'accéder à ses propriétés -->
    <div v-if="form" class="p-6">
      <!-- Bannière -->
      <div class="mb-6 rounded-lg overflow-hidden">
        <img
          :src="`/images/logo.svg`"
          :alt="form.title"
          class="w-full h-48 object-contain"
        />
      </div>

      <!-- Contenu du formulaire -->
      <div class="max-w-3xl mx-auto">
        <!-- En-tête -->
        <div class="mb-8 text-center">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ form.title }}
          </h2>
          <p v-if="form.description" class="mt-2 text-gray-600">
            {{ form.description }}
          </p>
        </div>

        <!-- Progression -->
        <div v-if="form.sections" class="mb-6">
          <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>
              Section {{ currentSection + 1 }} sur
              {{ form.sections.length }}
            </span>
            <span>
              {{
                Math.round(((currentSection + 1) / form.sections.length) * 100)
              }}%
            </span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
              :style="`width: ${
                ((currentSection + 1) / form.sections.length) * 100
              }%`"
            ></div>
          </div>
        </div>

        <!-- Section courante -->
        <div v-if="currentSectionData && shouldShowSection" class="mb-8">
          <h3 class="text-xl font-semibold mb-4">
            {{ currentSectionData.title }}
          </h3>
          <p v-if="currentSectionData.description" class="text-gray-600 mb-6">
            {{ currentSectionData.description }}
          </p>

          <!-- Questions -->
          <div class="space-y-6">
            <div
              v-for="question in currentSectionData.questions"
              :key="question.id"
              class="border-b pb-6"
            >
              <label class="block mb-2 font-medium">
                {{ question.question }}
                <span v-if="question.is_required" class="text-red-500">*</span>
              </label>

              <!-- Input en fonction du type -->
              <div class="mt-2">
                <!-- Texte court -->
                <input
                  v-if="question.type === 'text'"
                  v-model="answers[question.id]"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  :required="question.is_required"
                />

                <!-- Texte long -->
                <textarea
                  v-else-if="question.type === 'textarea'"
                  v-model="answers[question.id]"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  :required="question.is_required"
                ></textarea>

                <!-- Choix multiples -->
                <div v-else-if="question.type === 'checkbox'" class="space-y-2">
                  <div
                    v-for="option in question.options"
                    :key="option"
                    class="flex items-center"
                  >
                    <input
                      type="checkbox"
                      :value="option"
                      v-model="answers[question.id]"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4"
                      :required="
                        question.is_required &&
                        (!answers[question.id] ||
                          answers[question.id].length === 0)
                      "
                    />
                    <label class="ml-3 text-gray-700">{{ option }}</label>
                  </div>
                </div>

                <!-- Choix unique -->
                <div v-else-if="question.type === 'radio'" class="space-y-2">
                  <div
                    v-for="option in question.options"
                    :key="option"
                    class="flex items-center"
                  >
                    <input
                      type="radio"
                      :name="`question-${question.id}`"
                      :value="option"
                      v-model="answers[question.id]"
                      class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                      :required="question.is_required"
                    />
                    <label class="ml-3 text-gray-700">{{ option }}</label>
                  </div>
                </div>

                <!-- Liste déroulante -->
                <select
                  v-else-if="question.type === 'select'"
                  v-model="answers[question.id]"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  :required="question.is_required"
                >
                  <option value="">Sélectionnez une option</option>
                  <option
                    v-for="option in question.options"
                    :key="option"
                    :value="option"
                  >
                    {{ option }}
                  </option>
                </select>

                <!-- Note (étoiles) -->
                <div
                  v-else-if="question.type === 'rating'"
                  class="flex items-center space-x-1"
                >
                  <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    class="text-2xl focus:outline-none"
                    :class="
                      (answers[question.id] || 0) >= star
                        ? 'text-yellow-400'
                        : 'text-gray-300'
                    "
                    @click="answers[question.id] = star"
                  >
                    ★
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between">
          <SecondaryButton
            @click="previousSection"
            :disabled="currentSection === 0"
          >
            Précédent
          </SecondaryButton>

          <SecondaryButton
            v-if="!isLastSection"
            @click="nextSection"
            :disabled="!canProceed"
          >
            Suivant
          </SecondaryButton>

          <SecondaryButton
            v-else
            @click="
              $emit('close');
              resetPreview();
            "
          >
            Fermer la prévisualisation
          </SecondaryButton>
        </div>
      </div>
    </div>
  </Modal>
</template>
