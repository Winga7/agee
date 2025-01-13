<template>
    <AppLayout title="Envoi groupé d'évaluations">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Module</label>
                        <select v-model="form.module_id" @change="loadGroups" class="border p-2 w-full">
                            <option value="">Sélectionner un module</option>
                            <option v-for="module in modules" :key="module.id" :value="module.id">
                                {{ module.title }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4" v-if="groups.length">
                        <label class="block font-medium mb-1">Groupe</label>
                        <select v-model="form.class_group" class="border p-2 w-full">
                            <option value="">Sélectionner un groupe</option>
                            <option v-for="group in groups" :key="group" :value="group">
                                {{ group }}
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Envoyer les évaluations
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
