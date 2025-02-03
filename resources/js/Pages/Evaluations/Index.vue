<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    evaluations: Array,
    modules: Array
});

const showCreateModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    module_id: '',
    score: '',
    comment: ''
});

const editEvaluation = (evaluation) => {
    isEditing.value = true;
    form.id = evaluation.id;
    form.module_id = evaluation.module_id;
    form.score = evaluation.score;
    form.comment = evaluation.comment;
    showCreateModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('evaluations.update', form.id), {
            onSuccess: () => {
                resetForm();
                showCreateModal.value = false;
            },
        });
    } else {
        form.post(route('evaluations.store'), {
            onSuccess: () => {
                resetForm();
                showCreateModal.value = false;
            },
        });
    }
};

const resetForm = () => {
    form.reset();
    isEditing.value = false;
};

const confirmDelete = (evaluation) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette évaluation ?')) {
        form.delete(route('evaluations.destroy', evaluation.id));
    }
};
</script>

<template>
    <AppLayout title="Gestion des évaluations">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des évaluations
                </h2>
                <PrimaryButton @click="showCreateModal = true">
                    Nouvelle évaluation
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commentaire</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="evaluation in evaluations" :key="evaluation.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ evaluation.module ? evaluation.module.title : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ evaluation.score }}</td>
                                <td class="px-6 py-4">{{ evaluation.comment }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button @click="editEvaluation(evaluation)" class="text-indigo-600 hover:text-indigo-900">
                                            Modifier
                                        </button>
                                        <button @click="confirmDelete(evaluation)" class="text-red-600 hover:text-red-900">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de création/édition -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? "Modifier l'évaluation" : "Nouvelle évaluation" }}
                </h3>

                <form @submit.prevent="submitForm">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="module_id" value="Module" />
                            <select
                                id="module_id"
                                v-model="form.module_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Sélectionner un module</option>
                                <option v-for="module in modules" :key="module.id" :value="module.id">
                                    {{ module.title }}
                                </option>
                            </select>
                            <InputError :message="form.errors.module_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="score" value="Score" />
                            <TextInput
                                id="score"
                                type="number"
                                v-model="form.score"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.score" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="comment" value="Commentaire" />
                            <textarea
                                id="comment"
                                v-model="form.comment"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.comment" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-2">
                        <SecondaryButton @click="showCreateModal = false">
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ form.processing ? "Enregistrement..." : (isEditing ? "Modifier" : "Créer") }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
