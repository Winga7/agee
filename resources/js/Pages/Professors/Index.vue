<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SortableColumn from "@/Components/SortableColumn.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import ProfessorDetailsModal from "@/Components/ProfessorDetailsModal.vue";

const props = defineProps({
  professors: Array,
});

const showCreateModal = ref(false);
const isEditing = ref(false);
const searchQuery = ref("");
const currentSort = ref({ field: "last_name", direction: "asc" });
const showDetailsModal = ref(false);
const selectedProfessor = ref(null);

const form = useForm({
  id: null,
  first_name: "",
  last_name: "",
  email: "",
  school_email: "",
  telephone: "",
  adress: "",
  birth_date: "",
});

const filteredAndSortedProfessors = computed(() => {
  let result = [...props.professors];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(
      (professor) =>
        professor.first_name.toLowerCase().includes(query) ||
        professor.last_name.toLowerCase().includes(query) ||
        professor.email.toLowerCase().includes(query)
    );
  }

  result.sort((a, b) => {
    const aValue = a[currentSort.value.field];
    const bValue = b[currentSort.value.field];

    if (currentSort.value.direction === "asc") {
      return aValue > bValue ? 1 : -1;
    }
    return aValue < bValue ? 1 : -1;
  });

  return result;
});

const resetForm = () => {
  form.reset();
  isEditing.value = false;
};

const editProfessor = (professor) => {
  isEditing.value = true;
  form.id = professor.id;
  form.first_name = professor.first_name;
  form.last_name = professor.last_name;
  form.email = professor.email;
  form.school_email = professor.school_email;
  form.telephone = professor.telephone;
  form.adress = professor.adress;
  form.birth_date = professor.birth_date;
  showCreateModal.value = true;
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(route("professors.update", form.id), {
      onSuccess: () => {
        resetForm();
        showCreateModal.value = false;
      },
    });
  } else {
    form.post(route("professors.store"), {
      onSuccess: () => {
        resetForm();
        showCreateModal.value = false;
      },
    });
  }
};

const confirmDelete = (professor) => {
  if (confirm("Êtes-vous sûr de vouloir supprimer ce professeur ?")) {
    form.delete(route("professors.destroy", professor.id));
  }
};

const handleSort = (sortData) => {
  currentSort.value = sortData;
};

const viewDetails = (professor) => {
  selectedProfessor.value = professor;
  showDetailsModal.value = true;
};
</script>

<template>
  <AppLayout title="Gestion des professeurs">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestion des professeurs
        </h2>
        <PrimaryButton @click="showCreateModal = true">
          Ajouter un professeur
        </PrimaryButton>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <SearchFilter
            placeholder="Rechercher un professeur..."
            @search="(query) => (searchQuery = query)"
          />

          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <SortableColumn
                  label="Nom"
                  field="last_name"
                  :current-sort="currentSort"
                  @sort="handleSort"
                />
                <SortableColumn
                  label="Prénom"
                  field="first_name"
                  :current-sort="currentSort"
                  @sort="handleSort"
                />
                <SortableColumn
                  label="Email"
                  field="email"
                  :current-sort="currentSort"
                  @sort="handleSort"
                />
                <th
                  class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Modules
                </th>
                <th
                  class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="professor in filteredAndSortedProfessors"
                :key="professor.id"
              >
                <td class="px-6 py-4">{{ professor.last_name }}</td>
                <td class="px-6 py-4">{{ professor.first_name }}</td>
                <td class="px-6 py-4">{{ professor.email }}</td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="module in professor.modules"
                      :key="module.id"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      {{ module.title }}
                    </span>
                    <span
                      v-if="!professor.modules.length"
                      class="text-gray-400"
                    >
                      Aucun module
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex space-x-2">
                    <PrimaryButton @click="viewDetails(professor)">
                      Voir détails
                    </PrimaryButton>
                    <button
                      @click="editProfessor(professor)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Modifier
                    </button>
                    <button
                      @click="confirmDelete(professor)"
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
    <Modal :show="showCreateModal" @close="showCreateModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ isEditing ? "Modifier le professeur" : "Ajouter un professeur" }}
        </h3>

        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <InputLabel for="last_name" value="Nom" />
              <TextInput
                id="last_name"
                v-model="form.last_name"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.last_name" class="mt-2" />
            </div>

            <div>
              <InputLabel for="first_name" value="Prénom" />
              <TextInput
                id="first_name"
                v-model="form.first_name"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.first_name" class="mt-2" />
            </div>

            <div>
              <InputLabel for="email" value="Email" />
              <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div>
              <InputLabel for="school_email" value="Email professionnel" />
              <TextInput
                id="school_email"
                v-model="form.school_email"
                type="email"
                class="mt-1 block w-full"
              />
              <InputError :message="form.errors.school_email" class="mt-2" />
            </div>

            <div>
              <InputLabel for="telephone" value="Téléphone" />
              <TextInput
                id="telephone"
                v-model="form.telephone"
                type="tel"
                class="mt-1 block w-full"
              />
              <InputError :message="form.errors.telephone" class="mt-2" />
            </div>

            <div>
              <InputLabel for="birth_date" value="Date de naissance" />
              <TextInput
                id="birth_date"
                v-model="form.birth_date"
                type="date"
                class="mt-1 block w-full"
              />
              <InputError :message="form.errors.birth_date" class="mt-2" />
            </div>

            <div class="col-span-2">
              <InputLabel for="adress" value="Adresse" />
              <textarea
                id="adress"
                v-model="form.adress"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                rows="3"
              ></textarea>
              <InputError :message="form.errors.adress" class="mt-2" />
            </div>
          </div>

          <div class="flex justify-end mt-6 space-x-2">
            <SecondaryButton @click="showCreateModal = false">
              Annuler
            </SecondaryButton>
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

    <ProfessorDetailsModal
      :show="showDetailsModal"
      :professor="selectedProfessor"
      @close="showDetailsModal = false"
    />
  </AppLayout>
</template>
