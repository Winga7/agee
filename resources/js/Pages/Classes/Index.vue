<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

// Composants importés
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SortableColumn from "@/Components/SortableColumn.vue";
import SearchFilter from "@/Components/SearchFilter.vue";

// Props
const props = defineProps({
  classGroups: { type: Array, required: true },
  modules: { type: Array, required: true },
});

// États réactifs
const showCreateModal = ref(false);
const isEditing = ref(false);
const searchQuery = ref("");
const currentSort = ref({ field: "name", direction: "asc" });

// Formulaire
const form = useForm({
  name: "",
  oldName: "",
});

// Fonctions utilitaires
const closeModal = () => {
  showCreateModal.value = false;
  isEditing.value = false;
  form.reset();
  form.clearErrors();
};

const handleSort = (sortData) => {
  currentSort.value = sortData;
};

// Computed properties
const modulesByClass = computed(() => {
  const groupedModules = {};
  props.classGroups.forEach((classGroup) => {
    groupedModules[classGroup.name] = classGroup.modules || [];
  });
  return groupedModules;
});

const filteredAndSortedClasses = computed(() => {
  let result = props.classGroups;

  // Filtrage par recherche
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((group) => group.name.toLowerCase().includes(query));
  }

  // Tri
  result.sort((a, b) => {
    if (currentSort.value.direction === "asc") {
      return a.name > b.name ? 1 : -1;
    }
    return a.name < b.name ? 1 : -1;
  });

  return result;
});

// Gestion des actions CRUD
const editClass = (group) => {
  isEditing.value = true;
  form.name = group.name;
  form.oldName = group.name;
  showCreateModal.value = true;
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(route("classes.update", form.oldName), {
      onSuccess: () => closeModal(),
      onError: (errors) => logError("modification", errors),
    });
  } else {
    form.post(route("classes.store"), {
      onSuccess: () => closeModal(),
      onError: (errors) => logError("création", errors),
    });
  }
};

const confirmDelete = (group) => {
  if (confirm("Êtes-vous sûr de vouloir supprimer cette classe ?")) {
    const deleteForm = useForm({});
    deleteForm.delete(route("classes.destroy", group.id));
  }
};

// Fonction helper pour la gestion des erreurs
const logError = (action, errors) => {
  axios.post("/api/log", {
    message: `Erreur lors de la ${action} de la classe`,
    data: {
      errors,
      className: action === "création" ? form.name : form.oldName,
    },
    level: "error",
  });
};
</script>

<template>
  <AppLayout title="Gestion des classes">
    <!-- En-tête de la page -->
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestion des classes
        </h2>
        <PrimaryButton @click="showCreateModal = true">
          Créer une classe
        </PrimaryButton>
      </div>
    </template>

    <!-- Contenu principal -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Barre de recherche -->
          <SearchFilter
            placeholder="Rechercher une classe..."
            @search="(query) => (searchQuery = query)"
          />

          <!-- Table des classes -->
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <SortableColumn
                  label="Nom de la classe"
                  field="name"
                  :current-sort="currentSort"
                  @sort="handleSort"
                />
                <th
                  class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Modules associés
                </th>
                <th
                  class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="group in filteredAndSortedClasses" :key="group.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ group.name }}</td>
                <td class="px-6 py-4">
                  <span
                    v-for="module in group.modules"
                    :key="module.id"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-1"
                  >
                    {{ module.title }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex space-x-2">
                    <button
                      @click="editClass(group)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Modifier
                    </button>
                    <button
                      @click="confirmDelete(group)"
                      class="text-red-600 hover:text-red-900"
                    >
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
    <Modal :show="showCreateModal" @close="closeModal">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ isEditing ? "Modifier la classe" : "Créer une classe" }}
        </h3>

        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div>
              <InputLabel for="name" value="Nom de la classe" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>
          </div>

          <div class="flex justify-end mt-6 space-x-2">
            <SecondaryButton @click="closeModal"> Annuler </SecondaryButton>
            <PrimaryButton type="submit" :disabled="form.processing">
              {{
                form.processing
                  ? "Enregistrement..."
                  : isEditing
                  ? "Modifier"
                  : "Créer"
              }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>
