<script setup>
import { ref, watch } from "vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
  placeholder: {
    type: String,
    default: "Rechercher...",
  },
});

const emit = defineEmits(["search"]);
const searchQuery = ref("");

watch(
  searchQuery,
  (newValue) => {
    emit("search", newValue);
  },
  { debounce: 300 }
);
</script>

<template>
  <div class="mb-4">
    <TextInput
      v-model="searchQuery"
      type="text"
      :placeholder="placeholder"
      class="w-full sm:w-64"
    />
  </div>
</template>
