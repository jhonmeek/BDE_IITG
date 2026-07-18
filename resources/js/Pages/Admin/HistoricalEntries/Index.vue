<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

defineProps({ entries: Array });

const form = useForm({
    title: '',
    period_label: '',
    event_date: '',
    content: '',
    sort_order: 0,
    is_published: true,
    image: null,
});

const submit = () => form.post(route('admin.historical-entries.index'));
const remove = (id) => router.delete(route('admin.historical-entries.destroy', id));
</script>

<template>
    <AdminLayout title="Historique">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Titre</th>
                            <th class="px-5 py-4">Periode</th>
                            <th class="px-5 py-4">Ordre</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="entry in entries" :key="entry.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ entry.title }}</td>
                            <td class="px-5 py-4">{{ entry.period_label }}</td>
                            <td class="px-5 py-4">{{ entry.sort_order }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="route('admin.historical-entries.edit', entry.id)" class="text-amber-700">Modifier</a>
                                <button class="ml-3 text-rose-700" @click="remove(entry.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Nouvelle entree</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <input v-model="form.title" type="text" class="input-shell" placeholder="Titre" />
                    <input v-model="form.period_label" type="text" class="input-shell" placeholder="Periode" />
                    <input v-model="form.event_date" type="date" class="input-shell" />
                    <input v-model="form.sort_order" type="number" class="input-shell" placeholder="Ordre" />
                    <textarea v-model="form.content" rows="5" class="input-shell" placeholder="Contenu" />
                    <input type="file" class="input-shell" @input="form.image = $event.target.files[0]" />
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.is_published" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                        Visible publiquement
                    </label>
                    <button class="btn-primary w-full" :disabled="form.processing">Enregistrer</button>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
