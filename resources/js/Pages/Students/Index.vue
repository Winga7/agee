<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
  students: {
    type: Array,
    required: true,
  },
  modules: {
    type: Array,
    required: true,
  },
  classes: {
    type: Array,
    required: true,
  },
  classGroups: {
    type: Array,
    required: true,
  },
});

const showCreateModal = ref(false);
const isEditing = ref(false);
const showEnrollmentModal = ref(false);
const selectedStudent = ref(null);

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
  student_id: null,
  module_id: "",
  class_group: "",
  start_date: "",
  end_date: "",
});

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
  form.school_email = student.school_email;
  form.student_id = student.student_number;
  form.birth_date = student.birth_date;
  form.telephone = student.phone_number || "";

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
  showEnrollmentModal.value = true;
};

const submitEnrollment = () => {
  enrollmentForm.post(route("enrollments.store"), {
    onSuccess: () => {
      showEnrollmentModal.value = false;
    },
  });
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
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Nom
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Numéro étudiant
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Email
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="student in students" :key="student.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ student.last_name }} {{ student.first_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ student.student_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ student.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
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
                    </div>
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
                v-model="form.email"
                type="email"
                class="mt-1 block w-full"
                required
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
            </div>

            <div>
              <InputLabel value="Classe" />
              <select
                v-model="enrollmentForm.class_group"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              >
                <option value="">Sélectionner une classe</option>
                <option
                  v-for="group in classGroups"
                  :key="group"
                  :value="group"
                >
                  {{ group }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <InputLabel value="Date de début" />
                <TextInput
                  type="date"
                  v-model="enrollmentForm.start_date"
                  class="mt-1 block w-full"
                />
              </div>
              <div>
                <InputLabel value="Date de fin" />
                <TextInput
                  type="date"
                  v-model="enrollmentForm.end_date"
                  class="mt-1 block w-full"
                />
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click="showEnrollmentModal = false">
              Annuler
            </SecondaryButton>
            <PrimaryButton type="submit" :disabled="enrollmentForm.processing">
              Enregistrer l'inscription
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>
