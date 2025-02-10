<script setup>
const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  field: {
    type: String,
    required: true,
  },
  currentSort: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["sort"]);

const toggleSort = () => {
  const direction =
    props.currentSort.field === props.field
      ? props.currentSort.direction === "asc"
        ? "desc"
        : "asc"
      : "asc";

  emit("sort", {
    field: props.field,
    direction: direction,
  });
};
</script>

<template>
  <th @click="toggleSort" class="px-6 py-3 cursor-pointer hover:bg-gray-50">
    <div class="flex items-center space-x-1">
      <span>{{ label }}</span>
      <span v-if="currentSort.field === field" class="text-gray-500">
        {{ currentSort.direction === "asc" ? "↑" : "↓" }}
      </span>
    </div>
  </th>
</template>
