<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    document: Object,
    categories: Array,
});

const form = useForm({
    title: props.document.title,
    category: props.document.category,
    file: null,
    is_public: props.document.is_public,
});

const submit = () => form.post(route('admin.documents.update', props.document.id), { method: 'put' });
</script>

<template>
    <AdminLayout title="Modifier un document">
        <section class="shell-card max-w-3xl p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <input v-model="form.title" type="text" class="input-shell" placeholder="Titre" />
                <select v-model="form.category" class="input-shell">
                    <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                </select>
                <input type="file" class="input-shell" @input="form.file = $event.target.files[0]" />
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input v-model="form.is_public" type="checkbox" class="rounded border-slate-300 text-amber-600" />
                    Rendre public
                </label>
                <button class="btn-primary" :disabled="form.processing">Mettre a jour</button>
            </form>
        </section>
    </AdminLayout>
</template>
