<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { formatDateTime } from '@/composables/useFormatters';

const props = defineProps({
    documents: Array,
    categories: Array,
});

const form = useForm({
    title: '',
    category: props.categories[0],
    file: null,
    is_public: false,
});

const submit = () => form.post(route('admin.documents.index'));
const remove = (id) => router.delete(route('admin.documents.destroy', id));
</script>

<template>
    <AdminLayout title="Documents">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Titre</th>
                            <th class="px-5 py-4">Categorie</th>
                            <th class="px-5 py-4">Visibilite</th>
                            <th class="px-5 py-4">Ajout</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="document in documents" :key="document.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ document.title }}</td>
                            <td class="px-5 py-4">{{ document.category }}</td>
                            <td class="px-5 py-4">{{ document.is_public ? 'Public' : 'Interne' }}</td>
                            <td class="px-5 py-4">{{ formatDateTime(document.created_at) }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="document.download_url" target="_blank" class="text-sky-700">Voir</a>
                                <a :href="route('admin.documents.edit', document.id)" class="ml-3 text-amber-700">Modifier</a>
                                <button class="ml-3 text-rose-700" @click="remove(document.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Ajouter un document</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <input v-model="form.title" type="text" class="input-shell" placeholder="Titre" />
                    <select v-model="form.category" class="input-shell">
                        <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                    </select>
                    <input type="file" class="input-shell" @input="form.file = $event.target.files[0]" />
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.is_public" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                        Rendre public
                    </label>
                    <button class="btn-primary w-full" :disabled="form.processing">Enregistrer</button>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
