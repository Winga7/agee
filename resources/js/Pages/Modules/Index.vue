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
    modules: Array,
    professors: Array,
    classGroups: Array
});

const showCreateModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    title: '',
    code: '',
    professor_id: null,
    description: '',
    class_groups: [],
});

const resetForm = () => {
    form.reset();
    isEditing.value = false;
};

const editModule = (module) => {
    isEditing.value = true;
    form.id = module.id;
    form.title = module.title;
    form.code = module.code;
    form.professor_id = module.professor_id;
    form.description = module.description;
    form.class_groups = module.classes.map(c => c.class_group);
    showCreateModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('modules.update', form.id), {
            onSuccess: () => {
                resetForm();
                showCreateModal.value = false;
            },
        });
    } else {
        form.post(route('modules.store'), {
            onSuccess: () => {
                resetForm();
                showCreateModal.value = false;
            },
        });
    }
};

const confirmDelete = (module) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce module ?')) {
        form.delete(route('modules.destroy', module.id));
    }
};
</script>

<template>
    <AppLayout title="Gestion des modules">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des modules
                </h2>
                <PrimaryButton @click="showCreateModal = true">
                    Créer un module
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professeur</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classes</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="module in modules" :key="module.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ module.title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ module.code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ module.professor ? `${module.professor.first_name} ${module.professor.last_name}` : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-for="class_group in module.classes" :key="class_group.id"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-1">
                                        {{ class_group.class_group }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button @click="editModule(module)" class="text-indigo-600 hover:text-indigo-900">
                                            Modifier
                                        </button>
                                        <button @click="confirmDelete(module)" class="text-red-600 hover:text-red-900">
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
                    {{ isEditing ? "Modifier le module" : "Créer un module" }}
                </h3>

                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="title" value="Titre" />
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="code" value="Code" />
                            <TextInput id="code" v-model="form.code" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.code" class="mt-2" />
                        </div>

                        <div class="col-span-2">
                            <InputLabel for="professor_id" value="Professeur" />
                            <select v-model="form.professor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option :value="null">-- Aucun --</option>
                                <option v-for="prof in professors" :key="prof.id" :value="prof.id">
                                    {{ prof.first_name }} {{ prof.last_name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.professor_id" class="mt-2" />
                        </div>

                        <div class="col-span-2">
                            <InputLabel value="Classes" />
                            <div class="mt-2 space-y-2">
                                <label v-for="group in classGroups" :key="group" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="group"
                                        v-model="form.class_groups"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-2">{{ group }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.class_groups" class="mt-2" />
                        </div>

                        <div class="col-span-2">
                            <InputLabel for="description" value="Description" />
                            <textarea v-model="form.description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
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
