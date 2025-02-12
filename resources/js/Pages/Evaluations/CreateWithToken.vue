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

const nextSection = () => {
  if (currentSection.value < props.form.sections.length - 1) {
    currentSection.value++;
  }
};

const previousSection = () => {
  if (currentSection.value > 0) {
    currentSection.value--;
  }
};

const submit = () => {
  form.answers = answers.value;
  form.post(route("evaluations.store-with-token", props.token));
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
        <h1 class="text-2xl font-bold text-gray-900 mb-6">
          Évaluation du cours : {{ module.title }}
        </h1>

        <div v-if="props.form && props.form.sections" class="mb-6">
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
