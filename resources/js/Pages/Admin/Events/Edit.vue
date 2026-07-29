<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ event: Object });

const form = useForm({
    name: props.event.name,
    slug: props.event.slug,
    location: props.event.location,
    excerpt: props.event.excerpt ?? '',
    description: props.event.description,
    starts_at: props.event.starts_at,
    budget_allocated: props.event.budget_allocated,
    capacity: props.event.capacity,
    participants_count: props.event.participants_count,
    registration_enabled: props.event.registration_enabled,
    is_published: props.event.is_published,
    cover_image: null,
});

const submit = () => form.post(route('admin.events.update', props.event.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier un événement">
        <section class="shell-card card-pad max-w-3xl">
            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.name" type="text" class="input-shell" placeholder="Nom" />
                <input v-model="form.slug" type="text" class="input-shell" placeholder="slug" />
                <input v-model="form.location" type="text" class="input-shell" placeholder="Lieu" />
                <input v-model="form.starts_at" type="datetime-local" class="input-shell" />
                <input v-model="form.excerpt" type="text" class="input-shell" placeholder="Résumé" />
                <textarea v-model="form.description" rows="5" class="input-shell" placeholder="Description" />
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
                <button class="btn-primary" :disabled="form.processing">Mettre à jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
