<script setup>
defineProps({
    sections: {
        type: Array,
        required: true
    },
    currentSection: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['update:currentSection'])
</script>

<template>
    <div class="flex items-center justify-between mb-6 bg-gray-50 p-4 rounded-lg">
        <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            :disabled="currentSection === 0"
            @click="emit('update:currentSection', currentSection - 1)"
        >
            Précédent
        </button>

        <div class="flex space-x-2">
            <button
                v-for="(section, index) in sections"
                :key="index"
                type="button"
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                :class="[
                    currentSection === index
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                ]"
                @click="emit('update:currentSection', index)"
            >
                {{ index + 1 }}
            </button>
        </div>

        <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            :disabled="currentSection === sections.length - 1"
            @click="emit('update:currentSection', currentSection + 1)"
        >
            Suivant
        </button>
    </div>
</template>
