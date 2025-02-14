<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SortableColumn from "@/Components/SortableColumn.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import StudentDetailsModal from "@/Components/StudentDetailsModal.vue";

const props = defineProps({
  students: Array,
  modules: Array,
  classes: {
    type: Array,
    required: true,
  },
  classGroups: Array,
});

const showCreateModal = ref(false);
const isEditing = ref(false);
const showEnrollmentModal = ref(false);
const selectedStudent = ref(null);
const showDetailsModal = ref(false);
const searchQuery = ref("");
const currentSort = ref({ field: "last_name", direction: "asc" });

const form = useForm({
  id: null,
  last_name: "",
  first_name: "",
  email: "",
  school_email: "",
  telephone: "",
  birth_date: "",
  student_id: "",
  class_id: "",
  academic_year: "",
  status: "active",
});

const enrollmentForm = useForm({
  student_id: selectedStudent?.id || "",
  module_id: "",
  class_id: "",
  start_date: "",
  end_date: "",
});

// Filtrage et tri des étudiants
const filteredAndSortedStudents = computed(() => {
  let result = [...props.students];

  // Filtrage
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(
      (student) =>
        student.first_name.toLowerCase().includes(query) ||
        student.last_name.toLowerCase().includes(query) ||
        student.email.toLowerCase().includes(query) ||
        student.student_number.toLowerCase().includes(query)
    );
  }

  // Tri
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

const handleSort = (sortData) => {
  currentSort.value = sortData;
};

const resetForm = () => {
  form.reset();
  isEditing.value = false;
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(route("students.update", form.id), {
      onSuccess: () => {
        resetForm();
        showCreateModal.value = false;
      },
    });
  } else {
    form.post(route("students.store"), {
      onSuccess: () => {
        resetForm();
        showCreateModal.value = false;
      },
    });
  }
};

const editStudent = (student) => {
  form.id = student.id;
  form.first_name = student.first_name;
  form.last_name = student.last_name;
  form.email = student.email;
  form.school_email = student.school_email || "";
  form.telephone = student.telephone || "";
  form.birth_date = student.birth_date;
  form.student_id = student.student_number;
  form.class_id = student.class?.id || "";
  form.academic_year = student.academic_year;
  form.status = student.status;

  isEditing.value = true;
  showCreateModal.value = true;
};

const confirmDelete = (student) => {
  if (confirm("Êtes-vous sûr de vouloir supprimer cet étudiant ?")) {
    const deleteForm = useForm({});
    deleteForm.delete(route("students.destroy", student.id));
  }
};

const manageEnrollments = (student) => {
  selectedStudent.value = student;
  enrollmentForm.student_id = student.id;
  showEnrollmentModal.value = true;
};

const submitEnrollment = () => {
  enrollmentForm.post(route("enrollments.store"), {
    onSuccess: () => {
      showEnrollmentModal.value = false;
      enrollmentForm.reset();
    },
    preserveScroll: true,
  });
};

const viewDetails = (student) => {
  selectedStudent.value = student;
  showDetailsModal.value = true;
};
</script>

<template>
  <AppLayout title="Gestion des étudiants">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestion des étudiants
        </h2>
        <PrimaryButton @click="showCreateModal = true">
          Ajouter un étudiant
        </PrimaryButton>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <SearchFilter
            placeholder="Rechercher un étudiant..."
            @search="(query) => (searchQuery = query)"
          />

          <div
            v-if="students.length === 0"
            class="text-center text-gray-500 py-8"
          >
            Aucun étudiant n'a été ajouté pour le moment.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
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
                  <SortableColumn
                    label="N° Étudiant"
                    field="student_number"
                    :current-sort="currentSort"
                    @sort="handleSort"
                  />
                  <th class="px-6 py-3">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="student in filteredAndSortedStudents"
                  :key="student.id"
                >
                  <td class="px-6 py-4">{{ student.last_name }}</td>
                  <td class="px-6 py-4">{{ student.first_name }}</td>
                  <td class="px-6 py-4">{{ student.email }}</td>
                  <td class="px-6 py-4">{{ student.student_number }}</td>
                  <td class="px-6 py-4 space-x-2">
                    <PrimaryButton @click="viewDetails(student)">
                      Voir détails
                    </PrimaryButton>
                    <button
                      @click="editStudent(student)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Modifier
                    </button>
                    <button
                      @click="manageEnrollments(student)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Inscriptions
                    </button>
                    <button
                      @click="confirmDelete(student)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de création/édition -->
    <Modal :show="showCreateModal" @close="showCreateModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ isEditing ? "Modifier l'étudiant" : "Ajouter un étudiant" }}
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
              <InputLabel for="schoolemail" value="School-Email" />
              <TextInput
                id="schoolemail"
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
                type="text"
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
                required
              />
              <InputError :message="form.errors.birth_date" class="mt-2" />
            </div>

            <div>
              <InputLabel for="student_id" value="Numéro étudiant" />
              <TextInput
                id="student_id"
                v-model="form.student_id"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.student_id" class="mt-2" />
            </div>

            <div>
              <InputLabel for="class_id" value="Classe" />
              <select
                v-model="form.class_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              >
                <option value="">Sélectionner une classe</option>
                <option
                  v-for="classe in classes"
                  :key="classe.id"
                  :value="classe.id"
                >
                  {{ classe.name }}
                </option>
              </select>
              <InputError :message="form.errors.class_id" class="mt-2" />
            </div>

            <div>
              <InputLabel for="academic_year" value="Année scolaire" />
              <TextInput
                id="academic_year"
                v-model="form.academic_year"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.academic_year" class="mt-2" />
            </div>

            <div>
              <InputLabel for="status" value="Statut" />
              <select
                v-model="form.status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              >
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
                <option value="graduated">Diplômé</option>
              </select>
              <InputError :message="form.errors.status" class="mt-2" />
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
                  : "Ajouter"
              }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal de gestion des inscriptions -->
    <Modal :show="showEnrollmentModal" @close="showEnrollmentModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          Gérer les inscriptions - {{ selectedStudent?.first_name }}
          {{ selectedStudent?.last_name }}
        </h3>

        <form @submit.prevent="submitEnrollment">
          <div class="space-y-4">
            <div>
              <InputLabel value="Module" />
              <select
                v-model="enrollmentForm.module_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                required
              >
                <option value="">Sélectionner un module</option>
                <option
                  v-for="module in modules"
                  :key="module.id"
                  :value="module.id"
                >
                  {{ module.title }}
                </option>
              </select>
              <InputError
                :message="enrollmentForm.errors.module_id"
                class="mt-2"
              />
            </div>

            <div>
              <InputLabel value="Groupe de classe" />
              <select
                v-model="enrollmentForm.class_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                required
              >
                <option value="">Sélectionner un groupe</option>
                <option
                  v-for="classGroup in classes"
                  :key="classGroup.id"
                  :value="classGroup.id"
                >
                  {{ classGroup.name }}
                </option>
              </select>
              <InputError
                :message="enrollmentForm.errors.class_id"
                class="mt-2"
              />
            </div>

            <div>
              <InputLabel value="Date de début" />
              <TextInput
                v-model="enrollmentForm.start_date"
                type="date"
                class="mt-1 block w-full"
                required
              />
              <InputError
                :message="enrollmentForm.errors.start_date"
                class="mt-2"
              />
            </div>

            <div>
              <InputLabel value="Date de fin" />
              <TextInput
                v-model="enrollmentForm.end_date"
                type="date"
                class="mt-1 block w-full"
                required
              />
              <InputError
                :message="enrollmentForm.errors.end_date"
                class="mt-2"
              />
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click="showEnrollmentModal = false">
              Annuler
            </SecondaryButton>
            <PrimaryButton type="submit" :disabled="enrollmentForm.processing">
              {{
                enrollmentForm.processing
                  ? "Enregistrement..."
                  : "Enregistrer l'inscription"
              }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal de détails de l'étudiant -->
    <StudentDetailsModal
      :show="showDetailsModal"
      :student="selectedStudent"
      @close="showDetailsModal = false"
    />
  </AppLayout>
</template>
