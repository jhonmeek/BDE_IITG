<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    collections: Array,
});

const form = useForm({
    title: props.item.title,
    collection: props.item.collection,
    media_type: props.item.media_type,
    caption: props.item.caption ?? '',
    external_url: props.item.external_url ?? '',
    sort_order: props.item.sort_order,
    is_published: props.item.is_published,
    file: null,
});

const submit = () => form.post(route('admin.media-assets.update', props.item.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier un media">
        <section class="shell-card max-w-3xl p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.title" type="text" class="input-shell" placeholder="Titre" />
                <select v-model="form.collection" class="input-shell">
                    <option v-for="collection in collections" :key="collection" :value="collection">{{ collection }}</option>
                </select>
                <select v-model="form.media_type" class="input-shell">
                    <option value="image">image</option>
                    <option value="video">video</option>
                </select>
                <textarea v-model="form.caption" rows="5" class="input-shell" placeholder="Legende" />
                <input v-model="form.external_url" type="url" class="input-shell" placeholder="URL externe optionnelle" />
                <input v-model="form.sort_order" type="number" class="input-shell" placeholder="Ordre" />
                <input type="file" class="input-shell" @input="form.file = $event.target.files[0]" />
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input v-model="form.is_published" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                    Visible publiquement
                </label>
                <button class="btn-primary" :disabled="form.processing">Mettre a jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
