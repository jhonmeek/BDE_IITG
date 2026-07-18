<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    items: Array,
    collections: Array,
});

const form = useForm({
    title: '',
    collection: props.collections[0],
    media_type: 'image',
    caption: '',
    external_url: '',
    sort_order: 0,
    is_published: true,
    file: null,
});

const submit = () => form.post(route('admin.media-assets.index'));
const remove = (id) => router.delete(route('admin.media-assets.destroy', id));
</script>

<template>
    <AdminLayout title="Medias">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="shell-card overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Titre</th>
                            <th class="px-5 py-4">Collection</th>
                            <th class="px-5 py-4">Type</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in items" :key="item.id">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ item.title }}</td>
                            <td class="px-5 py-4">{{ item.collection }}</td>
                            <td class="px-5 py-4">{{ item.media_type }}</td>
                            <td class="px-5 py-4 text-right">
                                <a :href="route('admin.media-assets.edit', item.id)" class="text-amber-700">Modifier</a>
                                <button class="ml-3 text-rose-700" @click="remove(item.id)">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="shell-card p-6">
                <h3 class="text-xl font-semibold">Ajouter un media</h3>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <input v-model="form.title" type="text" class="input-shell" placeholder="Titre" />
                    <select v-model="form.collection" class="input-shell">
                        <option v-for="collection in collections" :key="collection" :value="collection">{{ collection }}</option>
                    </select>
                    <select v-model="form.media_type" class="input-shell">
                        <option value="image">image</option>
                        <option value="video">video</option>
                    </select>
                    <textarea v-model="form.caption" rows="4" class="input-shell" placeholder="Legende" />
                    <input v-model="form.external_url" type="url" class="input-shell" placeholder="URL externe optionnelle" />
                    <input v-model="form.sort_order" type="number" class="input-shell" placeholder="Ordre" />
                    <input type="file" class="input-shell" @input="form.file = $event.target.files[0]" />
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
