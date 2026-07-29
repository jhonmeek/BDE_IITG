<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { slugify, formatDateTime } from '@/composables/useFormatters';

defineProps({ events: Array });

const form = useForm({
    name: '',
    slug: '',
    location: '',
    excerpt: '',
    description: '',
    starts_at: '',
    budget_allocated: 0,
    capacity: '',
    participants_count: 0,
    registration_enabled: true,
    is_published: true,
    cover_image: null,
});

const syncSlug = () => {
    form.slug = slugify(form.name);
};

const submit = () => form.post(route('admin.events.index'));
const remove = (id) => router.delete(route('admin.events.destroy', id));
</script>

<template>
    <AdminLayout title="Événements">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card table-scroll">
                <table class="min-w-full text-sm">
                    <thead class="table-head">
                        <tr>
                            <th class="px-5 py-4">Événement</th>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Lieu</th>
                            <th class="px-5 py-4">Inscriptions</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="event in events" :key="event.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ event.name }}</td>
                            <td class="px-5 py-4">{{ formatDateTime(event.starts_at) }}</td>
                            <td class="px-5 py-4">{{ event.location }}</td>
                            <td class="px-5 py-4">{{ event.registrations_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="route('admin.events.edit', event.id)" class="action-edit">Modifier</a>
                                <button class="action-delete ml-3" @click="remove(event.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-5 sm:p-6">
                <h3 class="text-lg font-semibold sm:text-xl">Nouvel événement</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <input v-model="form.name" type="text" class="input-shell" placeholder="Nom" @input="syncSlug" />
                    <input v-model="form.slug" type="text" class="input-shell" placeholder="slug" />
                    <input v-model="form.location" type="text" class="input-shell" placeholder="Lieu" />
                    <input v-model="form.starts_at" type="datetime-local" class="input-shell" />
                    <input v-model="form.excerpt" type="text" class="input-shell" placeholder="Résumé" />
                    <textarea v-model="form.description" rows="4" class="input-shell" placeholder="Description" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <input v-model="form.budget_allocated" type="number" class="input-shell" placeholder="Budget" />
                        <input v-model="form.capacity" type="number" class="input-shell" placeholder="Capacité" />
                    </div>
                    <input v-model="form.participants_count" type="number" class="input-shell" placeholder="Participants constatés" />
                    <input type="file" class="input-shell" @input="form.cover_image = $event.target.files[0]" />
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.registration_enabled" type="checkbox" class="field-check" />
                        Inscriptions ouvertes
                    </label>
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.is_published" type="checkbox" class="field-check" />
                        Visible publiquement
                    </label>
                    <button class="btn-primary w-full" :disabled="form.processing">Enregistrer</button>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
