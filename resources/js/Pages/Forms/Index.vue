<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import FormPreview from '@/Components/FormPreview.vue'

const props = defineProps({
    forms: {
        type: Array,
        required: true
    }
})

// État du formulaire
const showCreateModal = ref(false)
const isEditing = ref(false)
const currentSection = ref(0)
const previewBanner = ref(null)
const showPreviewModal = ref(false)
const selectedForm = ref(null)

const form = useForm({
    id: null,
    title: '',
    description: '',
    banner_image: null,
    sections: [{
        title: '',
        description: '',
        order: 0,
        depends_on_question_id: null,
        depends_on_answer: null,
        questions: [{
            question: '',
            type: 'text',
            options: [],
            order: 0,
            is_required: true,
            controls_visibility: false
        }]
    }]
})

const resetForm = () => {
    form.reset()
    isEditing.value = false
    previewBanner.value = null
    currentSection.value = 0
}

// Méthodes pour la gestion des sections
const addSection = () => {
    form.sections.push({
        title: '',
        description: '',
        order: form.sections.length,
        depends_on_question_id: null,
        depends_on_answer: null,
        questions: []
    })
}

const removeSection = (index) => {
    form.sections.splice(index, 1)
    form.sections.forEach((section, idx) => {
        section.order = idx
    })
}

// Méthodes pour la gestion des questions
const addQuestion = (sectionIndex) => {
    form.sections[sectionIndex].questions.push({
        question: '',
        type: 'text',
        options: [],
        order: form.sections[sectionIndex].questions.length,
        is_required: true,
        controls_visibility: false
    })
}

const removeQuestion = (sectionIndex, questionIndex) => {
    form.sections[sectionIndex].questions.splice(questionIndex, 1)
    form.sections[sectionIndex].questions.forEach((question, idx) => {
        question.order = idx
    })
}

// Gestion des options pour les questions radio/select
const addOption = (sectionIndex, questionIndex) => {
    if (!form.sections[sectionIndex].questions[questionIndex].options) {
        form.sections[sectionIndex].questions[questionIndex].options = []
    }
    form.sections[sectionIndex].questions[questionIndex].options.push('')
}

const removeOption = (sectionIndex, questionIndex, optionIndex) => {
    form.sections[sectionIndex].questions[questionIndex].options.splice(optionIndex, 1)
}

// Gestion de l'image bannière
const handleBannerUpload = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.banner_image = file
        const reader = new FileReader()
        reader.onload = e => previewBanner.value = e.target.result
        reader.readAsDataURL(file)
    }
}

// Soumission du formulaire
const submitForm = () => {
    if (isEditing.value) {
        form.put(route('forms.update', form.id), {
            onSuccess: () => {
                resetForm()
                showCreateModal.value = false
            }
        })
    } else {
        form.post(route('forms.store'), {
            onSuccess: () => {
                resetForm()
                showCreateModal.value = false
            }
        })
    }
}

const editForm = (formData) => {
    isEditing.value = true
    form.id = formData.id
    form.title = formData.title
    form.description = formData.description
    form.sections = formData.sections
    if (formData.banner_image) {
        previewBanner.value = `/storage/${formData.banner_image}`
    }
    showCreateModal.value = true
}

const confirmDelete = (form) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce formulaire ?')) {
        useForm().delete(route('forms.destroy', form.id), {
            preserveScroll: true
        })
    }
}

const toggleActive = (form) => {
    useForm().put(route('forms.toggle-active', form.id), {
        is_active: !form.is_active
    }, {
        preserveScroll: true
    })
}

const showPreview = (form) => {
    selectedForm.value = form
    showPreviewModal.value = true
}

const closePreview = () => {
    showPreviewModal.value = false
}
</script>

<template>
    <AppLayout title="Gestion des formulaires">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des formulaires
                </h2>
                <PrimaryButton @click="showCreateModal = true">
                    Créer un formulaire
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Liste des formulaires existants -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="forms.length === 0" class="text-center text-gray-500 py-8">
                        Aucun formulaire n'a été créé pour le moment.
                    </div>

                    <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="form in forms" :key="form.id" class="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="relative h-40">
                                <img
                                    v-if="form.banner_image"
                                    :src="`/storage/${form.banner_image}`"
                                    :alt="form.title"
                                    class="w-full h-full object-cover"
                                >
                                <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ form.title }}</h3>
                                <p class="text-gray-600 text-sm mb-4">{{ form.description }}</p>

                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <span>{{ form.sections.length }} section(s)</span>
                                    <span>{{ form.sections.reduce((acc, section) => acc + section.questions.length, 0) }} question(s)</span>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="editForm(form)"
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Modifier
                                        </button>

                                        <button
                                            @click="confirmDelete(form)"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Supprimer
                                        </button>

                                        <button
                                            @click="showPreview(form)"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Prévisualiser
                                        </button>
                                    </div>

                                    <div class="flex items-center">
                                        <label class="flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :checked="form.is_active"
                                                @change="toggleActive(form)"
                                                class="sr-only"
                                            >
                                            <div class="relative w-10 h-5 bg-gray-200 rounded-full transition-colors duration-200 ease-in-out"
                                                :class="{ 'bg-green-400': form.is_active }">
                                                <div class="absolute left-0 w-5 h-5 bg-white rounded-full transition-transform duration-200 ease-in-out transform"
                                                    :class="{ 'translate-x-5': form.is_active }"></div>
                                            </div>
                                            <span class="ml-2 text-sm text-gray-600">{{ form.is_active ? 'Actif' : 'Inactif' }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de création/édition -->
        <Modal :show="showCreateModal" @close="() => { showCreateModal = false; resetForm(); }">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Modifier le formulaire' : 'Créer un nouveau formulaire' }}
                </h3>

                <form @submit.prevent="submitForm">
                    <!-- Informations générales -->
                    <div class="mb-6">
                        <InputLabel for="title" value="Titre" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.title" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <InputLabel for="description" value="Description" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            rows="3"
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <InputLabel for="banner" value="Image bannière" />
                        <input
                            type="file"
                            id="banner"
                            @change="handleBannerUpload"
                            accept="image/*"
                            class="mt-1 block w-full"
                        />
                        <img v-if="previewBanner" :src="previewBanner" class="mt-2 max-h-32" />
                        <InputError :message="form.errors.banner_image" class="mt-2" />
                    </div>

                    <!-- Sections -->
                    <div v-for="(section, sectionIndex) in form.sections" :key="sectionIndex" class="mb-8 p-4 border rounded-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-medium">Section {{ sectionIndex + 1 }}</h4>
                            <DangerButton @click="removeSection(sectionIndex)" type="button">
                                Supprimer la section
                            </DangerButton>
                        </div>

                        <div class="mb-4">
                            <InputLabel :for="'section-title-'+sectionIndex" value="Titre de la section" />
                            <TextInput
                                :id="'section-title-'+sectionIndex"
                                v-model="section.title"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <div class="mb-4">
                            <InputLabel :for="'section-desc-'+sectionIndex" value="Description de la section" />
                            <textarea
                                :id="'section-desc-'+sectionIndex"
                                v-model="section.description"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                rows="2"
                            />
                        </div>

                        <!-- Questions de la section -->
                        <div v-for="(question, questionIndex) in section.questions" :key="questionIndex" class="mb-4 p-4 border rounded">
                            <div class="flex justify-between items-center mb-2">
                                <h5 class="font-medium">Question {{ questionIndex + 1 }}</h5>
                                <DangerButton @click="removeQuestion(sectionIndex, questionIndex)" type="button" size="sm">
                                    Supprimer
                                </DangerButton>
                            </div>

                            <div class="mb-2">
                                <InputLabel :for="'question-'+sectionIndex+'-'+questionIndex" value="Question" />
                                <TextInput
                                    :id="'question-'+sectionIndex+'-'+questionIndex"
                                    v-model="question.question"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>

                            <div class="mb-2">
                                <InputLabel :for="'type-'+sectionIndex+'-'+questionIndex" value="Type de réponse" />
                                <select
                                    :id="'type-'+sectionIndex+'-'+questionIndex"
                                    v-model="question.type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="text">Texte court</option>
                                    <option value="textarea">Texte long</option>
                                    <option value="radio">Choix unique</option>
                                    <option value="checkbox">Choix multiples</option>
                                    <option value="select">Liste déroulante</option>
                                    <option value="rating">Note (étoiles)</option>
                                </select>
                            </div>

                            <!-- Options pour radio/select -->
                            <div v-if="['radio', 'select'].includes(question.type)" class="mt-2">
                                <div v-for="(option, optionIndex) in question.options" :key="optionIndex" class="flex items-center gap-2 mb-2">
                                    <TextInput
                                        v-model="question.options[optionIndex]"
                                        type="text"
                                        class="flex-1"
                                        placeholder="Option de réponse"
                                    />
                                    <button @click="removeOption(sectionIndex, questionIndex, optionIndex)" type="button" class="text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <SecondaryButton @click="addOption(sectionIndex, questionIndex)" type="button" class="mt-2">
                                    Ajouter une option
                                </SecondaryButton>
                            </div>

                            <div class="mt-2 flex items-center gap-4">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="question.is_required"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Obligatoire</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="question.controls_visibility"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Contrôle la visibilité</span>
                                </label>
                            </div>
                        </div>

                        <PrimaryButton @click="addQuestion(sectionIndex)" type="button" class="mt-2">
                            Ajouter une question
                        </PrimaryButton>
                    </div>

                    <div class="flex justify-between mt-6">
                        <SecondaryButton @click="addSection" type="button">
                            Ajouter une section
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ form.processing
                                ? (isEditing ? 'Modification...' : 'Création...')
                                : (isEditing ? 'Modifier le formulaire' : 'Créer le formulaire')
                            }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <FormPreview
            :form="selectedForm"
            :show="showPreviewModal"
            @close="closePreview"
        />
    </AppLayout>
</template>
