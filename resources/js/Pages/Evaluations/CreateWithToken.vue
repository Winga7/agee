<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
  token: String,
  module: Object,
  expires_at: String,
  form: Object,
});

const currentSection = ref(0);
const answers = ref({});

const form = useForm({
  answers: {},
  token: props.token,
});

const currentSectionData = computed(() => {
  if (!props.form || !props.form.sections || props.form.sections.length === 0) {
    return null;
  }
  return props.form.sections[currentSection.value];
});

const isLastSection = computed(() => {
  return currentSection.value === props.form.sections.length - 1;
});

const canProceed = computed(() => {
  if (!currentSectionData.value || !currentSectionData.value.questions) {
    return false;
  }

  const requiredQuestions = currentSectionData.value.questions.filter(
    (q) => q.is_required
  );
  return requiredQuestions.every(
    (q) => answers.value[q.id] !== undefined && answers.value[q.id] !== ""
  );
});

// Ajouter cette computed property pour vérifier si une section doit être affichée
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

// Modifier la fonction nextSection
const nextSection = () => {
  if (
    !canProceed.value ||
    currentSection.value >= props.form.sections.length - 1
  )
    return;

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

// Modifier la fonction previousSection
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

const submit = () => {
  form.answers = answers.value;
  form.post(route("evaluations.store-with-token", props.token), {
    onSuccess: () => {
      // Redirection gérée par le contrôleur
      console.log("Formulaire soumis avec succès");
    },
    onError: (errors) => {
      console.error("Erreurs lors de la soumission:", errors);
    },
    preserveScroll: true,
  });
};

const initializeAnswers = () => {
  if (!props.form || !props.form.sections) return;

  props.form.sections.forEach((section) => {
    section.questions.forEach((question) => {
      if (question.type === "checkbox") {
        answers.value[question.id] = [];
      } else {
        answers.value[question.id] = "";
      }
    });
  });
};

// Appeler la fonction lors du montage du composant
onMounted(() => {
  initializeAnswers();
});
</script>

<template>
  <PublicLayout :title="`Évaluation - ${module.title}`">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white p-6 shadow-sm rounded-lg">
        <!-- Ajout de la bannière avec le logo -->
        <div class="mb-6 rounded-lg overflow-hidden">
          <img
            src="/images/logo.svg"
            :alt="module.title"
            class="w-full h-48 object-contain"
          />
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-6">
          Évaluation du cours : {{ module.title }}
        </h1>

        <div
          v-if="props.form && props.form.sections && shouldShowSection"
          class="mb-6"
        >
          <h2 class="text-xl font-semibold mb-4">
            {{ currentSectionData?.title }}
          </h2>

          <div
            v-for="question in currentSectionData.questions"
            :key="question.id"
            class="mb-6"
          >
            <InputLabel :for="'q_' + question.id">
              {{ question.question }}
              <span v-if="question.is_required" class="text-red-500">*</span>
            </InputLabel>

            <!-- Différents types de questions -->
            <div class="mt-2">
              <!-- Note sur 5 -->
              <div v-if="question.type === 'rating'" class="flex space-x-2">
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

              <!-- Texte court -->
              <input
                v-else-if="question.type === 'text'"
                :id="'q_' + question.id"
                v-model="answers[question.id]"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                :required="question.is_required"
              />

              <!-- Zone de texte -->
              <textarea
                v-else-if="question.type === 'textarea'"
                :id="'q_' + question.id"
                v-model="answers[question.id]"
                rows="4"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                :required="question.is_required"
              >
              </textarea>

              <!-- Radio buttons -->
              <div v-else-if="question.type === 'radio'" class="mt-2 space-y-2">
                <div
                  v-for="option in question.options"
                  :key="option"
                  class="flex items-center"
                >
                  <input
                    :id="`q_${question.id}_${option}`"
                    type="radio"
                    :name="`q_${question.id}`"
                    :value="option"
                    v-model="answers[question.id]"
                    class="h-4 w-4 text-indigo-600 border-gray-300"
                    :required="question.is_required"
                  />
                  <label
                    :for="`q_${question.id}_${option}`"
                    class="ml-2 block text-sm text-gray-700"
                  >
                    {{ option }}
                  </label>
                </div>
              </div>

              <!-- Select -->
              <select
                v-else-if="question.type === 'select'"
                :id="'q_' + question.id"
                v-model="answers[question.id]"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
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

              <!-- Checkbox -->
              <div
                v-else-if="question.type === 'checkbox'"
                class="mt-2 space-y-2"
              >
                <div
                  v-for="option in question.options"
                  :key="option"
                  class="flex items-center"
                >
                  <input
                    :id="`q_${question.id}_${option}`"
                    type="checkbox"
                    :value="option"
                    v-model="answers[question.id]"
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                  />
                  <label
                    :for="`q_${question.id}_${option}`"
                    class="ml-2 block text-sm text-gray-700"
                  >
                    {{ option }}
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <div class="flex justify-between mt-6">
            <SecondaryButton
              @click="previousSection"
              :disabled="currentSection === 0"
            >
              Précédent
            </SecondaryButton>

            <div>
              <SecondaryButton
                v-if="!isLastSection"
                @click="nextSection"
                :disabled="!canProceed"
                class="mr-2"
              >
                Suivant
              </SecondaryButton>
              <PrimaryButton
                v-if="isLastSection"
                @click="submit"
                :disabled="!canProceed || form.processing"
              >
                {{ form.processing ? "Envoi..." : "Soumettre l'évaluation" }}
              </PrimaryButton>
            </div>
          </div>
        </div>
        <div v-else class="text-red-600">
          Le formulaire d'évaluation n'est pas disponible.
        </div>

        <pre class="mt-4 p-4 bg-gray-100 rounded">
          {{ props.form }}
        </pre>
      </div>
    </div>
  </PublicLayout>
</template>
