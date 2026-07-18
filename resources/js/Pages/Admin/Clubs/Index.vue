<script setup>
import InputError from '@/Components/InputError.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { slugify } from '@/composables/useFormatters';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    clubs: Array,
    categories: Array,
    statuses: Array,
});

const form = useForm({
    name: '',
    slug: '',
    category: props.categories[0],
    lead_name: '',
    summary: '',
    description: '',
    budget_allocated: 0,
    status: props.statuses[0],
    is_published: true,
    image: null,
});

const syncSlug = () => {
    form.slug = slugify(form.name);
};

const submit = () => form.post(route('admin.clubs.index'));
const remove = (id) => router.delete(route('admin.clubs.destroy', id));
</script>

<template>
    <AdminLayout title="Clubs">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Club</th>
                            <th class="px-5 py-4">Categorie</th>
                            <th class="px-5 py-4">Responsable</th>
                            <th class="px-5 py-4">Inscriptions</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="club in clubs" :key="club.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ club.name }}</td>
                            <td class="px-5 py-4">{{ club.category }}</td>
                            <td class="px-5 py-4">{{ club.lead_name }}</td>
                            <td class="px-5 py-4">{{ club.registrations_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="route('admin.clubs.edit', club.id)" class="text-amber-700">Modifier</a>
                                <button class="ml-3 text-rose-700" @click="remove(club.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Ajouter un club</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <input v-model="form.name" type="text" class="input-shell" placeholder="Nom du club" @input="syncSlug" />
                    <InputError :message="form.errors.name" />
                    <input v-model="form.slug" type="text" class="input-shell" placeholder="slug" />
                    <select v-model="form.category" class="input-shell">
                        <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                    </select>
                    <input v-model="form.lead_name" type="text" class="input-shell" placeholder="Responsable" />
                    <input v-model="form.summary" type="text" class="input-shell" placeholder="Resume court" />
                    <textarea v-model="form.description" rows="4" class="input-shell" placeholder="Description" />
                    <input v-model="form.budget_allocated" type="number" class="input-shell" placeholder="Budget" />
                    <select v-model="form.status" class="input-shell">
                        <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                    </select>
                    <input type="file" class="input-shell" @input="form.image = $event.target.files[0]" />
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.is_published" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                        Club visible publiquement
                    </label>
                    <button class="btn-primary w-full" :disabled="form.processing">Enregistrer</button>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
