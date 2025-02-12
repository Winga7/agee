<script setup>
import { Head } from "@inertiajs/vue3";

defineProps({
  title: String,
});
</script>

<template>
  <Head :title="title" />

  <div class="min-h-screen bg-gray-100">
    <main>
      <div class="py-12">
        <slot />
      </div>
    </main>
  </div>
</template>
