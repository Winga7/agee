<script setup>
import { useForm } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
  token: String,
  module: Object,
  expires_at: String,
  form: Object,
});

const form = useForm({
  score: "",
  comment: "",
});

const submit = () => {
  form.post(route("evaluations.store-with-token", props.token));
};
</script>

<template>
  <PublicLayout :title="`Évaluation - ${module.title}`">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white p-6 shadow-sm rounded-lg">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">
          Évaluation du cours : {{ module.title }}
        </h1>

        <form @submit.prevent="submit">
          <div class="mb-6">
            <InputLabel for="score" value="Note (1-5)" />
            <TextInput
              id="score"
              type="number"
              min="1"
              max="5"
              class="mt-1 block w-full"
              v-model="form.score"
              required
            />
            <InputError :message="form.errors.score" class="mt-2" />
          </div>

          <div class="mb-6">
            <InputLabel for="comment" value="Commentaire (optionnel)" />
            <textarea
              id="comment"
              v-model="form.comment"
              class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
              rows="4"
            ></textarea>
            <InputError :message="form.errors.comment" class="mt-2" />
          </div>

          <div class="flex justify-end">
            <PrimaryButton :disabled="form.processing">
              Soumettre l'évaluation
            </PrimaryButton>
          </div>
        </form>
      </div>
    </div>
  </PublicLayout>
</template>
