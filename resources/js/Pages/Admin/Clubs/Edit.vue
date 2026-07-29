<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    club: Object,
    categories: Array,
    statuses: Array,
});

const form = useForm({
    name: props.club.name,
    slug: props.club.slug,
    category: props.club.category,
    lead_name: props.club.lead_name,
    summary: props.club.summary ?? '',
    description: props.club.description,
    budget_allocated: props.club.budget_allocated,
    status: props.club.status,
    is_published: props.club.is_published,
    image: null,
});

const submit = () => form.post(route('admin.clubs.update', props.club.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier un club">
        <section class="shell-card card-pad max-w-3xl">
            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.name" type="text" class="input-shell" placeholder="Nom du club" />
                <input v-model="form.slug" type="text" class="input-shell" placeholder="slug" />
                <select v-model="form.category" class="input-shell">
                    <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                </select>
                <input v-model="form.lead_name" type="text" class="input-shell" placeholder="Responsable" />
                <input v-model="form.summary" type="text" class="input-shell" placeholder="Résumé court" />
                <textarea v-model="form.description" rows="5" class="input-shell" placeholder="Description" />
                <input v-model="form.budget_allocated" type="number" class="input-shell" placeholder="Budget" />
                <select v-model="form.status" class="input-shell">
                    <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                </select>
                <input type="file" class="input-shell" @input="form.image = $event.target.files[0]" />
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input v-model="form.is_published" type="checkbox" class="field-check" />
                    Club visible publiquement
                </label>
                <button class="btn-primary" :disabled="form.processing">Mettre à jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
