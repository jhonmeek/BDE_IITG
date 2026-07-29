<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ entry: Object });

const form = useForm({
    title: props.entry.title,
    period_label: props.entry.period_label,
    event_date: props.entry.event_date,
    content: props.entry.content,
    sort_order: props.entry.sort_order,
    is_published: props.entry.is_published,
    image: null,
});

const submit = () => form.post(route('admin.historical-entries.update', props.entry.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier une entrée historique">
        <section class="shell-card card-pad max-w-3xl">
            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.title" type="text" class="input-shell" placeholder="Titre" />
                <input v-model="form.period_label" type="text" class="input-shell" placeholder="Période" />
                <input v-model="form.event_date" type="date" class="input-shell" />
                <input v-model="form.sort_order" type="number" class="input-shell" placeholder="Ordre" />
                <textarea v-model="form.content" rows="6" class="input-shell" placeholder="Contenu" />
                <input type="file" class="input-shell" @input="form.image = $event.target.files[0]" />
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input v-model="form.is_published" type="checkbox" class="field-check" />
                    Visible publiquement
                </label>
                <button class="btn-primary" :disabled="form.processing">Mettre à jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
